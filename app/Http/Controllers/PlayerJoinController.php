<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Player;
use App\Models\EventWaitlist;
use App\Jobs\SendWaitlistOfferJob;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;
use Midtrans\Transaction;

class PlayerJoinController extends Controller
{
    public function show(Request $request, $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        $pendingCookieName = 'pending_payment_' . $event->id;
        $pendingJoin = json_decode($request->cookie($pendingCookieName), true);
        $pendingJoinPlayer = null;

        if (is_array($pendingJoin)
            && isset($pendingJoin['event_id'], $pendingJoin['player_id'])
            && (int) $pendingJoin['event_id'] === (int) $event->id
        ) {
            $pendingPlayer = $event->players()->where('players.id', $pendingJoin['player_id'])->first();
            if ($pendingPlayer && $pendingPlayer->pivot->payment_status !== 'paid') {
                $pendingJoinPlayer = $pendingPlayer;
            }
        }

        $joinedCount = $event->players()->wherePivot('status_join', 'joined')->count();
        $isFull = $joinedCount >= $event->slot_max;

        $rolesWithAvailability = [];
        $joinedPlayers = collect();

        if ($event->skema_iuran === 'custom') {
            $eventRoles = collect($event->roles ?? []);

            // helper to compute admin fee based on requested tier rules:
            // <=49_000 => +1_500
            // <=99_000 => +3_000
            // >=100_000 => +3% of base
            $computeAdminFee = function (float $base, string $method) {
                if ($method !== 'online_banking') {
                    return 0;
                }
                if ($base <= 49000) {
                    return 1500;
                }
                if ($base <= 99000) {
                    return 3000;
                }
                return (int) round($base * 0.03);
            };
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

            $rolesWithAvailability = $eventRoles->map(function ($role) use ($roleCounts, $event, $computeAdminFee) {
                $joinedForRole = $roleCounts[$role['name']] ?? 0;
                $slots = isset($role['slots']) ? (int) $role['slots'] : 0;
                $base = isset($role['price']) ? (float) $role['price'] : 0;
                $admin = $computeAdminFee($base, $event->metode_pembayaran);
                $display = $base + $admin;

                return array_merge($role, [
                    'joined' => $joinedForRole,
                    'slots_left' => max(0, $slots - $joinedForRole),
                    'is_full' => $slots <= 0 || $joinedForRole >= $slots,
                    'admin_fee' => $admin,
                    'display_price' => $display,
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

        $waitingCount = $event->waitlists()->where('status','waiting')->count();

        return view('player.join.show', compact('event', 'isFull', 'joinedCount', 'joinedPlayers', 'rolesWithAvailability', 'waitingCount', 'pendingJoinPlayer'));
    }

    public function store(Request $request, $slug, \App\Services\EventService $eventService)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        $pendingCookieName = 'pending_payment_' . $event->id;
        $pendingJoin = json_decode($request->cookie($pendingCookieName), true);
        if (is_array($pendingJoin)
            && isset($pendingJoin['event_id'], $pendingJoin['player_id'])
            && (int) $pendingJoin['event_id'] === (int) $event->id
        ) {
            $pendingPlayer = $event->players()->where('players.id', $pendingJoin['player_id'])->first();
            if ($pendingPlayer && $pendingPlayer->pivot->payment_status !== 'paid') {
                return back()->withInput()->with('error', 'Anda sudah memiliki pendaftaran tertunda pada event ini. Selesaikan pembayaran atau batalkan pendaftaran sebelumnya terlebih dahulu.');
            }
        }

        $joinedCount = $event->players()->wherePivot('status_join', 'joined')->count();
        $waitingCount = $event->waitlists()->where('status', 'waiting')->count();
        $availableSlots = max(0, (int) $event->slot_max - $joinedCount);
        $isFull = $joinedCount >= $event->slot_max;
        $hasWaitingList = $event->enable_waiting_list && ($waitingCount > 0 || $availableSlots <= 1);
        $shouldUseWaitlist = $event->enable_waiting_list && ($isFull || $hasWaitingList);

        $computeAdminFee = function (float $base, string $method) {
            if ($method !== 'online_banking') {
                return 0;
            }
            if ($base <= 49000) {
                return 1500;
            }
            if ($base <= 99000) {
                return 3000;
            }
            return (int) round($base * 0.03);
        };

        if ($shouldUseWaitlist) {
            if ($event->enable_waiting_list) {
                $rules = [
                    'nama' => 'required|string|max:255',
                    'kontak' => 'required|string|max:255',
                ];

                if ($event->skema_iuran === 'custom') {
                    $roleNames = array_column($event->roles ?? [], 'name');
                    $rules['role_name'] = ['required', 'string', Rule::in($roleNames)];
                }

                $validated = $request->validate($rules);

                // Find or create player record
                $player = Player::firstOrCreate(
                    ['kontak' => $validated['kontak']],
                    ['nama' => $validated['nama']]
                );

                if ($player->nama !== $validated['nama']) {
                    $player->update(['nama' => $validated['nama']]);
                }

                $roleName = null;
                $paymentAmount = (float) $event->iuran_per_pemain;

                if ($event->skema_iuran === 'custom') {
                    $roleName = $validated['role_name'];
                    $selectedRole = collect($event->roles ?? [])->firstWhere('name', $roleName);
                    if ($selectedRole) {
                        $paymentAmount = (float) ($selectedRole['price'] ?? $event->iuran_per_pemain);
                    }
                } elseif ($event->skema_iuran === 'loyalitas') {
                    $paymentAmount = (float) $eventService->calculateLoyaltyFee($event, $validated['kontak']);
                }

                $paymentAmount += $computeAdminFee($paymentAmount, $event->metode_pembayaran);

                $existing = EventWaitlist::where('event_id', $event->id)
                    ->where(function ($q) use ($player, $validated) {
                        $q->where('player_id', $player->id);
                        if (!empty($validated['kontak'])) {
                            $q->orWhere('phone', $validated['kontak']);
                        }
                    })->first();

                if (!$existing) {
                    EventWaitlist::create([
                        'event_id' => $event->id,
                        'player_id' => $player->id,
                        'phone' => $player->kontak,
                        'role_name' => $roleName,
                        'payment_amount' => $paymentAmount,
                        'status' => 'waiting',
                    ]);
                } else {
                    $existing->update([
                        'role_name' => $roleName,
                        'payment_amount' => $paymentAmount,
                        'status' => 'waiting',
                    ]);
                }

                $position = EventWaitlist::where('event_id', $event->id)->where('status','waiting')->count();

                $reasonParts = [];
                if ($isFull) {
                    $reasonParts[] = 'event sudah penuh';
                }
                if ($availableSlots <= 1 && !$isFull) {
                    $reasonParts[] = 'slot tersisa 1';
                }
                if ($waitingCount > 0) {
                    $reasonParts[] = 'sudah ada waiting list';
                }

                if (empty($reasonParts)) {
                    $reasonParts[] = 'event sedang menggunakan sistem antrean';
                }

                $reasonText = count($reasonParts) === 1
                    ? $reasonParts[0]
                    : implode(' dan ', $reasonParts);

                return back()->with('info', "Pendaftaran Anda diarahkan ke waiting list karena {$reasonText}. Anda berada di posisi {$position}.");
            }

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

        // compute admin fee using same tier rules as in show()
        $computeAdminFee = function (float $base, string $method) {
            if ($method !== 'online_banking') {
                return 0;
            }
            if ($base <= 49000) {
                return 1500;
            }
            if ($base <= 99000) {
                return 3000;
            }
            return (int) round($base * 0.03);
        };

        $adminFee = $computeAdminFee($baseFee, $event->metode_pembayaran);

        $fee = $baseFee + $adminFee;

        // Registrasi langsung disimpan sebagai joined; peserta dapat menyelesaikan pembayaran nanti.
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

        $eventStart = Carbon::parse($event->tanggal->format('Y-m-d') . ' ' . $event->waktu);
        $cookieMinutes = max(60, min(10080, now()->diffInMinutes($eventStart) + 1440));

        return redirect()->route('player.join.success', $event->slug)
            ->withCookie(cookie('pending_payment_' . $event->id, json_encode([
                'event_id' => $event->id,
                'player_id' => $player->id,
            ]), $cookieMinutes));
    }

    public function success(Request $request, $slug)
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

        if (!$latestJoin) {
            $pendingCookieName = 'pending_payment_' . $event->id;
            $pendingJoin = json_decode($request->cookie($pendingCookieName), true);

            if (is_array($pendingJoin)
                && isset($pendingJoin['event_id'], $pendingJoin['player_id'])
                && (int) $pendingJoin['event_id'] === (int) $event->id
            ) {
                $latestJoin = $event->players()->where('players.id', $pendingJoin['player_id'])->first();
            }
        }

        if ($latestJoin && $latestJoin->pivot->payment_status === 'pending' && $latestJoin->pivot->payment_reference) {
            $serverKey = config('services.midtrans.server_key');
            if ($serverKey) {
                MidtransConfig::$serverKey = $serverKey;
                MidtransConfig::$isProduction = (bool) config('services.midtrans.is_production', false);
                MidtransConfig::$isSanitized = true;
                MidtransConfig::$is3ds = true;

                try {
                    $statusResult = Transaction::status($latestJoin->pivot->payment_reference);
                    $transactionStatus = $statusResult->transaction_status ?? 'pending';
                    $newStatus = match ($transactionStatus) {
                        'capture', 'settlement' => 'paid',
                        'deny', 'cancel', 'expire' => 'failed',
                        default => 'pending',
                    };

                    if ($newStatus !== $latestJoin->pivot->payment_status) {
                        $payload = [
                            'payment_status' => $newStatus,
                            'payment_paid_at' => $newStatus === 'paid' ? now() : null,
                            'payment_expires_at' => $newStatus === 'paid' ? null : $latestJoin->pivot->payment_expires_at,
                            'payment_snap_token' => $newStatus === 'paid' ? null : $latestJoin->pivot->payment_snap_token,
                        ];

                        if ($newStatus === 'paid') {
                            $payload['status_join'] = 'joined';
                        }

                        $event->players()->updateExistingPivot($latestJoin->id, $payload);
                        
                        // Refresh the latestJoin model
                        $latestJoin = $event->players()->where('players.id', $latestJoin->id)->first();
                    }
                } catch (\Exception $e) {
                    // Ignore errors during automatic check
                }
            }
        }

        if ($latestJoin && $latestJoin->pivot->payment_status === 'paid') {
            $pendingCookieName = 'pending_payment_' . $event->id;
            return redirect()->route('player.join.show', $event->slug)
                ->with('success', 'Pembayaran berhasil! Anda telah bergabung dalam event.')
                ->withCookie(cookie()->forget($pendingCookieName));
        }

        return view('player.join.success', compact('event', 'latestJoin'));
    }

    public function cancel(Request $request, $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        $pendingCookieName = 'pending_payment_' . $event->id;
        $joinContext = session('join_context');
        $pendingJoin = json_decode($request->cookie($pendingCookieName), true);

        $playerId = null;

        if (
            is_array($joinContext)
            && isset($joinContext['event_id'], $joinContext['player_id'])
            && (int) $joinContext['event_id'] === (int) $event->id
        ) {
            $playerId = $joinContext['player_id'];
        } elseif (
            is_array($pendingJoin)
            && isset($pendingJoin['event_id'], $pendingJoin['player_id'])
            && (int) $pendingJoin['event_id'] === (int) $event->id
        ) {
            $playerId = $pendingJoin['player_id'];
        }

        if ($playerId) {
            $player = $event->players()->where('players.id', $playerId)->first();

            // Only cancel if payment is still pending — do not cancel a paid registration
            if ($player && $player->pivot->payment_status !== 'paid') {
                DB::transaction(function () use ($event, $playerId) {
                    $event->players()->detach($playerId);

                    // Promote next waiting list entry to a reminder-ready state for manual follow-up.
                    $next = $event->waitlists()->where('status','waiting')->oldest()->first();
                    if ($next) {
                        SendWaitlistOfferJob::dispatch($next);
                    }
                });
            }
        }

        session()->forget('join_context');

        return redirect()->route('player.join.show', $event->slug)
            ->with('info', 'Pendaftaran berhasil dibatalkan. Anda dapat mendaftar ulang kapan saja.')
            ->withCookie(cookie()->forget($pendingCookieName));
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
            'status_join' => 'joined',
            'payment_status' => 'paid',
            'payment_reference' => $reference,
            'payment_paid_at' => now(),
            'payment_expires_at' => null,
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

        $existingRef = $player->pivot->payment_reference;
        $existingToken = $player->pivot->payment_snap_token;
        $expiresAt = $player->pivot->payment_expires_at ? Carbon::parse($player->pivot->payment_expires_at) : null;

        if ($player->pivot->payment_status === 'pending' && $existingRef && $existingToken && $expiresAt && $expiresAt->greaterThan(now())) {
            return response()->json(['token' => $existingToken]);
        }

        $orderId = 'PSH-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6));
        $grossAmount = (int) $player->pivot->payment_amount;

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
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit' => 'minute',
                'duration' => 3,
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        $event->players()->updateExistingPivot($player->id, [
            'payment_reference' => $orderId,
            'payment_status' => 'pending',
            'payment_expires_at' => now()->addMinutes(3),
            'payment_snap_token' => $snapToken,
        ]);

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

        $updatePayload = [
            'payment_status' => $newStatus,
            'payment_reference' => $orderId,
            'payment_paid_at' => $newStatus === 'paid' ? now() : null,
            'payment_expires_at' => $newStatus === 'paid' ? null : $player->pivot->payment_expires_at,
            'payment_snap_token' => $newStatus === 'paid' ? null : $player->pivot->payment_snap_token,
        ];

        if ($newStatus === 'paid') {
            $updatePayload['status_join'] = 'joined';
        }

        // If the order id doesn't match the current player's pivot, attempt to find the player by order id
        $targetPlayer = $player;
        if ($player->pivot->payment_reference !== $orderId) {
            $found = $event->players()->wherePivot('payment_reference', $orderId)->first();
            if ($found) {
                $targetPlayer = $found;
            }
        }

        if (!$targetPlayer) {
            return response()->json(['message' => 'Pemain untuk order ini tidak ditemukan.'], 404);
        }

        $event->players()->updateExistingPivot($targetPlayer->id, $updatePayload);

        $response = response()->json(['status' => $newStatus]);

        if ($newStatus === 'paid') {
            session()->flash('success', 'Pembayaran berhasil! Anda telah bergabung dalam event.');
            $response->withCookie(cookie()->forget('pending_payment_' . $event->id));
        }

        return $response;
    }

    public function midtransStatus(Request $request, $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        $joinContext = session('join_context');
        $pendingCookieName = 'pending_payment_' . $event->id;
        $pendingJoin = json_decode($request->cookie($pendingCookieName), true);
        $playerIdFromRequest = $request->input('player_id');
        $paymentReferenceFromRequest = $request->input('payment_reference');

        if (!is_array($joinContext)
            || !isset($joinContext['event_id'], $joinContext['player_id'])
            || (int) $joinContext['event_id'] !== (int) $event->id
        ) {
            if (is_array($pendingJoin)
                && isset($pendingJoin['event_id'], $pendingJoin['player_id'])
                && (int) $pendingJoin['event_id'] === (int) $event->id
            ) {
                $joinContext = $pendingJoin;
            }
        }

        $player = null;
        if (is_array($joinContext) && isset($joinContext['player_id'])) {
            $player = $event->players()->where('players.id', $joinContext['player_id'])->first();
        }

        if (!$player && $playerIdFromRequest) {
            $player = $event->players()->where('players.id', $playerIdFromRequest)->first();
        }

        if (!$player && $paymentReferenceFromRequest) {
            $player = $event->players()->wherePivot('payment_reference', $paymentReferenceFromRequest)->first();
        }

        if (!$player) {
            return response()->json(['message' => 'Data pemain tidak ditemukan.'], 404);
        }

        if ($player->pivot->payment_method !== 'online_banking') {
            return response()->json(['message' => 'Metode pembayaran bukan online banking.'], 422);
        }

        if ($player->pivot->payment_status === 'paid') {
            return response()->json(['status' => 'paid']);
        }

        $reference = $player->pivot->payment_reference;
        if (!$reference) {
            return response()->json(['message' => 'Order belum dibuat.'], 422);
        }

        $serverKey = config('services.midtrans.server_key');
        if (!$serverKey) {
            return response()->json(['message' => 'Midtrans key belum dikonfigurasi.'], 500);
        }

        MidtransConfig::$serverKey = $serverKey;
        MidtransConfig::$isProduction = (bool) config('services.midtrans.is_production', false);
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = true;

        try {
            $statusResult = Transaction::status($reference);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memeriksa status pembayaran.', 'error' => $e->getMessage()], 500);
        }

        $transactionStatus = $statusResult->transaction_status ?? 'pending';
        $newStatus = match ($transactionStatus) {
            'capture', 'settlement' => 'paid',
            'deny', 'cancel', 'expire' => 'failed',
            default => 'pending',
        };

        $payload = [
            'payment_status' => $newStatus,
            'payment_reference' => $reference,
            'payment_paid_at' => $newStatus === 'paid' ? now() : null,
            'payment_expires_at' => $newStatus === 'paid' ? null : $player->pivot->payment_expires_at,
            'payment_snap_token' => $newStatus === 'paid' ? null : $player->pivot->payment_snap_token,
        ];

        if ($newStatus === 'paid') {
            $payload['status_join'] = 'joined';
        }

        $event->players()->updateExistingPivot($player->id, $payload);

        $response = response()->json(['status' => $newStatus, 'transaction_status' => $transactionStatus]);
        if ($newStatus === 'paid') {
            session()->flash('success', 'Pembayaran berhasil! Anda telah bergabung dalam event.');
            $response->withCookie(cookie()->forget($pendingCookieName));
        }

        return $response;
    }
}
