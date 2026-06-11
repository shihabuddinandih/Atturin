<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;

class PlayerJoinController extends Controller
{
    public function show(Request $request, $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        $pendingCookieName = 'pending_payment_' . $event->id;
        $pendingJoin = json_decode($request->cookie($pendingCookieName), true);

        if (is_array($pendingJoin)
            && isset($pendingJoin['event_id'], $pendingJoin['player_id'])
            && (int) $pendingJoin['event_id'] === (int) $event->id
        ) {
            $pendingPlayer = $event->players()->where('players.id', $pendingJoin['player_id'])->first();
            if ($pendingPlayer && $pendingPlayer->pivot->payment_method === 'online_banking' && $pendingPlayer->pivot->payment_status !== 'paid') {
                return redirect()->route('player.join.success', $event->slug);
            }
        }

        $joinedCount = $event->players()->wherePivot('status_join', 'joined')->count();
        $isFull = $joinedCount >= $event->slot_max;

        $rolesWithAvailability = [];
        $joinedPlayers = collect();

        if ($event->skema_iuran === 'custom') {
            $eventRoles = collect($event->roles ?? []);
            $joinedByRole = $event->players()
                ->wherePivot('status_join', 'joined')
                ->orderBy('event_player.created_at', 'asc')
                ->get(['players.id', 'players.nama', 'players.kontak', 'event_player.role_name', 'event_player.created_at']);

            $roleCounts = $joinedByRole->groupBy(function ($player) {
                return $player->pivot->role_name ?? 'Tanpa Role';
            })->map->count();

            $joinedPlayers = $joinedByRole->groupBy(function ($player) {
                return $player->pivot->role_name ?? 'Tanpa Role';
            });

            $rolesWithAvailability = $eventRoles->map(function ($role) use ($roleCounts) {
                $joinedForRole = $roleCounts[$role['name']] ?? 0;
                $slots = isset($role['slots']) ? (int) $role['slots'] : 0;

                return array_merge($role, [
                    'joined' => $joinedForRole,
                    'slots_left' => max(0, $slots - $joinedForRole),
                    'is_full' => $slots <= 0 || $joinedForRole >= $slots,
                ]);
            })->all();

            if (count($rolesWithAvailability) > 0 && collect($rolesWithAvailability)->every(fn ($role) => $role['is_full'])) {
                $isFull = true;
            }
        }

        if ($event->show_joined_players_public && $event->skema_iuran !== 'custom') {
            $joinedPlayers = $event->players()
                ->wherePivot('status_join', 'joined')
                ->orderBy('event_player.created_at', 'asc')
                ->get(['players.id', 'players.nama', 'players.kontak']);
        }

        return view('player.join.show', compact('event', 'isFull', 'joinedCount', 'joinedPlayers', 'rolesWithAvailability'));
    }

    public function store(Request $request, $slug, \App\Services\EventService $eventService)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        $joinedCount = $event->players()->wherePivot('status_join', 'joined')->count();
        if ($joinedCount >= $event->slot_max) {
            return back()->with('error', 'Maaf, slot pertandingan sudah penuh.');
        }

        $rules = [
            'nama' => 'required|string|max:255',
            'kontak' => 'required|string|max:255',
        ];

        if ($event->skema_iuran === 'custom') {
            $roleNames = array_column($event->roles ?? [], 'name');
            $rules['role_name'] = ['required', 'string', Rule::in($roleNames)];
        }

        $validated = $request->validate($rules);

        // Cek jika player dengan kontak yang sama sudah join di event ini
        $existingPlayer = $event->players()->where('kontak', $validated['kontak'])->first();
        if ($existingPlayer && $existingPlayer->pivot->status_join == 'joined') {
            return back()->with('error', 'Anda sudah bergabung pada pertandingan ini.');
        }

        // Cari atau buat player baru menggunakan data base kontak
        $player = Player::firstOrCreate(
            ['kontak' => $validated['kontak']],
            ['nama' => $validated['nama']]
        );

        // Update nama setiap kali kontak yang sama digunakan dengan nama terbaru
        if ($player->nama !== $validated['nama']) {
            $player->update(['nama' => $validated['nama']]);
        }

        if ($event->skema_iuran === 'custom') {
            $selectedRole = collect($event->roles ?? [])->firstWhere('name', $validated['role_name']);

            $selectedRoleSlots = isset($selectedRole['slots']) ? (int) $selectedRole['slots'] : 0;
            $selectedRoleJoinedCount = $event->players()
                ->wherePivot('status_join', 'joined')
                ->wherePivot('role_name', $validated['role_name'])
                ->count();

            if ($selectedRoleSlots > 0 && $selectedRoleJoinedCount >= $selectedRoleSlots) {
                return back()->withInput()->with('error', "Maaf, slot role {$validated['role_name']} sudah penuh. Silakan pilih role lain.");
            }

            $baseFee = $selectedRole ? (float) $selectedRole['price'] : (float) $event->iuran_per_pemain;
        } elseif ($event->skema_iuran === 'loyalitas') {
            $baseFee = $eventService->calculateLoyaltyFee($event, $validated['kontak']);
        } else {
            $baseFee = (float) $event->iuran_per_pemain;
        }

        $adminFee = 0;
        if ($event->metode_pembayaran === 'online_banking') {
            $adminFee = (int) round($baseFee * 0.03);
        }

        $fee = $baseFee + $adminFee;

        // Jika player sudah pernah join dan batal, update statusnya menjadi joined, jika belum attach baru
        $paymentPayload = [
            'status_join' => 'joined',
            'hadir' => false,
            'payment_method' => $event->metode_pembayaran,
            'payment_amount' => $fee,
            'payment_status' => 'pending',
            'payment_reference' => null,
            'payment_paid_at' => null,
        ];

        if ($event->skema_iuran === 'custom') {
            $paymentPayload['role_name'] = $validated['role_name'];
        }

        if ($existingPlayer) {
            $event->players()->updateExistingPivot($player->id, $paymentPayload);
        } else {
            $event->players()->attach($player->id, $paymentPayload);
        }

        session([
            'join_context' => [
                'event_id' => $event->id,
                'player_id' => $player->id,
            ],
        ]);

        return redirect()->route('player.join.success', $event->slug)
            ->withCookie(cookie()->forever('pending_payment_' . $event->id, json_encode([
                'event_id' => $event->id,
                'player_id' => $player->id,
            ])));
    }

    public function success($slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        $joinContext = session('join_context');
        $latestJoin = null;

        if (
            is_array($joinContext)
            && isset($joinContext['event_id'], $joinContext['player_id'])
            && (int) $joinContext['event_id'] === (int) $event->id
        ) {
            $latestJoin = $event->players()->where('players.id', $joinContext['player_id'])->first();
        }

        return view('player.join.success', compact('event', 'latestJoin'));
    }

    public function simulateOnlinePayment($slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        $joinContext = session('join_context');

        if (
            !is_array($joinContext)
            || !isset($joinContext['event_id'], $joinContext['player_id'])
            || (int) $joinContext['event_id'] !== (int) $event->id
        ) {
            return redirect()->route('player.join.show', $event->slug)
                ->with('error', 'Sesi pembayaran tidak ditemukan. Silakan join ulang.');
        }

        $player = $event->players()->where('players.id', $joinContext['player_id'])->first();
        if (!$player) {
            return redirect()->route('player.join.show', $event->slug)
                ->with('error', 'Data pemain tidak ditemukan. Silakan join ulang.');
        }

        if ($player->pivot->payment_method !== 'online_banking') {
            return back()->with('error', 'Metode pembayaran untuk event ini bukan online banking.');
        }

        if ($player->pivot->payment_status === 'paid') {
            return back()->with('success', 'Pembayaran Anda sudah tercatat sebagai PAID.')
                ->withCookie(cookie()->forget('pending_payment_' . $event->id));
        }

        $reference = 'SIM-MIDTRANS-' . strtoupper(Str::random(10));

        $event->players()->updateExistingPivot($player->id, [
            'payment_status' => 'paid',
            'payment_reference' => $reference,
            'payment_paid_at' => now(),
        ]);

        return back()->with('success', 'Simulasi pembayaran online berhasil. Status pembayaran Anda sudah PAID.');
    }

    public function midtransToken(Request $request, $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        $joinContext = session('join_context');

        if (
            !is_array($joinContext)
            || !isset($joinContext['event_id'], $joinContext['player_id'])
            || (int) $joinContext['event_id'] !== (int) $event->id
        ) {
            return response()->json(['message' => 'Sesi pembayaran tidak ditemukan.'], 422);
        }

        $player = $event->players()->where('players.id', $joinContext['player_id'])->first();
        if (!$player) {
            return response()->json(['message' => 'Data pemain tidak ditemukan.'], 404);
        }

        if ($player->pivot->payment_method !== 'online_banking') {
            return response()->json(['message' => 'Metode pembayaran bukan online banking.'], 422);
        }

        if ($player->pivot->payment_status === 'paid') {
            return response()->json(['message' => 'Pembayaran sudah tercatat.'], 200);
        }

        $serverKey = config('services.midtrans.server_key');
        $clientKey = config('services.midtrans.client_key');
        if (!$serverKey || !$clientKey) {
            return response()->json(['message' => 'Midtrans key belum dikonfigurasi.'], 500);
        }

        MidtransConfig::$serverKey = $serverKey;
        MidtransConfig::$isProduction = (bool) config('services.midtrans.is_production', false);
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = true;

        $orderId = 'PSH-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6));
        $grossAmount = (int) $player->pivot->payment_amount;

        $event->players()->updateExistingPivot($player->id, [
            'payment_reference' => $orderId,
            'payment_status' => 'pending',
        ]);

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'item_details' => [
                [
                    'id' => (string) $event->id,
                    'price' => $grossAmount,
                    'quantity' => 1,
                    'name' => $event->nama_event,
                ],
            ],
            'customer_details' => [
                'first_name' => $player->nama,
                'phone' => $player->kontak,
            ],
            'enabled_payments' => ['other_qris'],
        ];

        $snapToken = Snap::getSnapToken($params);

        return response()->json(['token' => $snapToken]);
    }

    public function midtransFinish(Request $request, $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        $joinContext = session('join_context');

        if (
            !is_array($joinContext)
            || !isset($joinContext['event_id'], $joinContext['player_id'])
            || (int) $joinContext['event_id'] !== (int) $event->id
        ) {
            return response()->json(['message' => 'Sesi pembayaran tidak ditemukan.'], 422);
        }

        $player = $event->players()->where('players.id', $joinContext['player_id'])->first();
        if (!$player) {
            return response()->json(['message' => 'Data pemain tidak ditemukan.'], 404);
        }

        $transactionStatus = (string) $request->input('transaction_status', 'pending');
        $orderId = $request->input('order_id', $player->pivot->payment_reference);

        $newStatus = match ($transactionStatus) {
            'capture', 'settlement' => 'paid',
            'deny', 'cancel', 'expire' => 'failed',
            default => 'pending',
        };

        $event->players()->updateExistingPivot($player->id, [
            'payment_status' => $newStatus,
            'payment_reference' => $orderId,
            'payment_paid_at' => $newStatus === 'paid' ? now() : null,
        ]);

        $response = response()->json(['status' => $newStatus]);

        if ($newStatus === 'paid') {
            $response->withCookie(cookie()->forget('pending_payment_' . $event->id));
        }

        return $response;
    }
}
