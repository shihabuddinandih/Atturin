<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;

class PlayerJoinController extends Controller
{
    public function show($slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        $joinedCount = $event->players()->wherePivot('status_join', 'joined')->count();
        $isFull = $joinedCount >= $event->slot_max;

        $joinedPlayers = collect();
        if ($event->show_joined_players_public) {
            $joinedPlayers = $event->players()
                ->wherePivot('status_join', 'joined')
                ->orderBy('event_player.created_at', 'asc')
                ->get(['players.id', 'players.nama', 'players.kontak']);
        }

        return view('player.join.show', compact('event', 'isFull', 'joinedCount', 'joinedPlayers'));
    }

    public function store(Request $request, $slug, \App\Services\EventService $eventService)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        $joinedCount = $event->players()->wherePivot('status_join', 'joined')->count();
        if ($joinedCount >= $event->slot_max) {
            return back()->with('error', 'Maaf, slot pertandingan sudah penuh.');
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kontak' => 'required|string|max:255',
        ]);

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

        $fee = $eventService->calculateLoyaltyFee($event, $validated['kontak']);

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

        return redirect()->route('player.join.success', $event->slug);
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
            return back()->with('success', 'Pembayaran Anda sudah tercatat sebagai PAID.');
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

        return response()->json(['status' => $newStatus]);
    }
}
