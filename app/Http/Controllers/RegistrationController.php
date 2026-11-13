<?php

namespace App\Http\Controllers;

use App\Jobs\SendWaitlistOfferJob;
use App\Models\Event;
use App\Models\Player;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;
use Midtrans\Transaction;

class RegistrationController extends Controller
{
    /**
     * Resolve the [Event, Player] pair for a registration token, regardless
     * of the visitor's session/cookies — this is what makes the link
     * shareable and durable across devices.
     */
    protected function resolveOrFail(string $token): array
    {
        $row = DB::table('event_player')->where('registration_token', $token)->first();
        abort_unless($row, 404);

        $event = Event::findOrFail($row->event_id);
        $player = $event->players()->where('players.id', $row->player_id)->first();
        abort_unless($player, 404);

        return [$event, $player];
    }

    public function show(Request $request, string $token)
    {
        [$event, $player] = $this->resolveOrFail($token);

        if ($player->pivot->payment_status === 'pending' && $player->pivot->payment_reference) {
            $serverKey = config('services.midtrans.server_key');
            if ($serverKey) {
                MidtransConfig::$serverKey = $serverKey;
                MidtransConfig::$isProduction = (bool) config('services.midtrans.is_production', false);
                MidtransConfig::$isSanitized = true;
                MidtransConfig::$is3ds = true;

                try {
                    $statusResult = Transaction::status($player->pivot->payment_reference);
                    $transactionStatus = $statusResult->transaction_status ?? 'pending';
                    $newStatus = match ($transactionStatus) {
                        'capture', 'settlement' => 'paid',
                        'deny', 'cancel', 'expire' => 'failed',
                        default => 'pending',
                    };

                    if ($newStatus !== $player->pivot->payment_status) {
                        $payload = [
                            'payment_status' => $newStatus,
                            'payment_paid_at' => $newStatus === 'paid' ? now() : null,
                            'payment_expires_at' => $newStatus === 'paid' ? null : $player->pivot->payment_expires_at,
                            'payment_snap_token' => $newStatus === 'paid' ? null : $player->pivot->payment_snap_token,
                        ];

                        if ($newStatus === 'paid') {
                            $payload['status_join'] = 'joined';
                        } elseif ($newStatus === 'failed' && $player->pivot->status_join === 'hold') {
                            $payload['status_join'] = 'batal';
                        }

                        $event->players()->updateExistingPivot($player->id, $payload);

                        $player = $event->players()->where('players.id', $player->id)->first();
                    }
                } catch (\Exception $e) {
                    // Ignore errors during automatic check
                }
            }
        }

        $player = $this->releaseExpiredHold($event, $player);

        $latestJoin = $player;
        $whatsappShareUrl = $this->buildWhatsAppShareUrl($player, $event, route('registration.show', $token));

        return view('player.registration.show', compact('event', 'latestJoin', 'token', 'whatsappShareUrl'));
    }

    public function midtransToken(Request $request, string $token)
    {
        [$event, $player] = $this->resolveOrFail($token);

        if ($player->pivot->payment_method !== 'online_banking') {
            return response()->json(['message' => 'Metode pembayaran bukan online banking.'], 422);
        }

        if ($player->pivot->payment_status === 'paid') {
            return response()->json(['message' => 'Pembayaran sudah tercatat.'], 200);
        }

        $player = $this->releaseExpiredHold($event, $player);
        if ($player->pivot->status_join === 'batal') {
            return response()->json(['message' => 'Waktu pembayaran habis dan slot sudah dilepas. Silakan daftar ulang.', 'expired' => true], 410);
        }

        // Masa tahan habis tapi masih dalam jeda verifikasi: jangan buat QR baru
        if ($player->pivot->status_join === 'hold'
            && $player->pivot->payment_expires_at
            && Carbon::parse($player->pivot->payment_expires_at)->isPast()) {
            return response()->json(['message' => 'Waktu pembayaran sudah habis. Jika Anda sudah membayar, status akan diperbarui otomatis dalam beberapa saat.'], 409);
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

        // Mode bayar dulu: QRIS tidak boleh berlaku lebih lama dari sisa masa tahan slot.
        $isHold = $player->pivot->status_join === 'hold';
        $durationMinutes = Event::HOLD_MINUTES;
        if ($isHold && $expiresAt) {
            $durationMinutes = (int) max(1, min(Event::HOLD_MINUTES, ceil(now()->diffInSeconds($expiresAt, false) / 60)));
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
                'duration' => $durationMinutes,
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        $event->players()->updateExistingPivot($player->id, [
            'payment_reference' => $orderId,
            'payment_status' => 'pending',
            // Masa tahan slot tidak diperpanjang saat membuat QR baru
            'payment_expires_at' => $isHold ? $expiresAt : now()->addMinutes(Event::HOLD_MINUTES),
            'payment_snap_token' => $snapToken,
        ]);

        return response()->json(['token' => $snapToken]);
    }

    public function midtransFinish(Request $request, string $token)
    {
        // Status dari browser (callback Snap) tidak dipercaya: selalu cek langsung ke Midtrans.
        return $this->midtransStatus($request, $token);
    }

    public function midtransStatus(Request $request, string $token)
    {
        [$event, $player] = $this->resolveOrFail($token);

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
        } elseif ($newStatus === 'failed' && $player->pivot->status_join === 'hold') {
            $payload['status_join'] = 'batal';
        }

        $event->players()->updateExistingPivot($player->id, $payload);

        if ($newStatus === 'pending') {
            $player = $this->releaseExpiredHold($event, $event->players()->where('players.id', $player->id)->first());
            if ($player->pivot->status_join === 'batal') {
                return response()->json(['status' => 'expired', 'transaction_status' => $transactionStatus]);
            }
        }

        $response = response()->json(['status' => $newStatus, 'transaction_status' => $transactionStatus]);
        if ($newStatus === 'paid') {
            session()->flash('success', 'Pembayaran berhasil! Anda telah bergabung dalam event.');
            $response->withCookie(cookie()->forget('pending_payment_' . $event->id));
        }

        return $response;
    }

    public function simulatePayment(Request $request, string $token)
    {
        // Hanya untuk pengembangan: di production endpoint ini akan menandai lunas tanpa membayar.
        abort_unless(app()->environment(['local', 'testing']), 404);

        [$event, $player] = $this->resolveOrFail($token);

        if ($player->pivot->payment_method !== 'online_banking') {
            return back()->with('error', 'Metode pembayaran untuk event ini bukan online banking.');
        }

        if ($player->pivot->payment_status === 'paid') {
            return back()->with('success', 'Pembayaran Anda sudah tercatat sebagai PAID.')
                ->withCookie(cookie()->forget('pending_payment_' . $event->id));
        }

        $player = $this->releaseExpiredHold($event, $player);
        if ($player->pivot->status_join === 'batal') {
            return back()->with('error', 'Waktu pembayaran habis dan slot sudah dilepas. Silakan daftar ulang.');
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

    public function cancel(Request $request, string $token)
    {
        [$event, $player] = $this->resolveOrFail($token);

        if ($player->pivot->payment_status !== 'paid') {
            DB::transaction(function () use ($event, $player) {
                $event->players()->detach($player->id);

                $next = $event->waitlists()->where('status', 'waiting')->oldest()->first();
                if ($next) {
                    SendWaitlistOfferJob::dispatch($next);
                }
            });
        }

        session()->forget('join_context');

        return redirect()->route('player.join.show', $event->slug)
            ->with('info', 'Pendaftaran berhasil dibatalkan. Anda dapat mendaftar ulang kapan saja.')
            ->withCookie(cookie()->forget('pending_payment_' . $event->id));
    }

    /**
     * Mode bayar dulu: jika masa tahan slot habis dan belum lunas, batalkan pendaftaran agar slot kembali tersedia.
     */
    protected function releaseExpiredHold(Event $event, Player $player): Player
    {
        $pivot = $player->pivot;

        if ($pivot->status_join !== 'hold' || $pivot->payment_status === 'paid') {
            return $player;
        }

        // Slot baru dilepas setelah masa tahan + jeda verifikasi pembayaran
        $releaseAt = Event::holdReleaseAt($pivot->payment_expires_at);
        if ($releaseAt && $releaseAt->isFuture()) {
            return $player;
        }

        $event->players()->updateExistingPivot($player->id, [
            'status_join' => 'batal',
            'payment_status' => 'failed',
            'payment_snap_token' => null,
        ]);

        if ($event->enable_waiting_list) {
            $next = $event->waitlists()->where('status', 'waiting')->oldest()->first();
            if ($next) {
                SendWaitlistOfferJob::dispatch($next);
            }
        }

        return $event->players()->where('players.id', $player->id)->first();
    }

    protected function buildWhatsAppShareUrl(Player $player, Event $event, string $registrationUrl): ?string
    {
        $phoneNumber = $this->normalizePhoneNumber($player->kontak);
        if (!$phoneNumber) {
            return null;
        }

        $message = "Halo {$player->nama}, pendaftaran Anda untuk event {$event->nama_event} sudah tercatat. " .
            "Simpan link berikut untuk melihat status, membayar, atau membatalkan pendaftaran kapan saja: {$registrationUrl}";

        return 'https://wa.me/' . $phoneNumber . '?text=' . urlencode($message);
    }

    protected function normalizePhoneNumber(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);
        if (empty($digits)) {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        if (str_starts_with($digits, '62')) {
            return $digits;
        }

        if (str_starts_with($digits, '+62')) {
            return substr($digits, 1);
        }

        return $digits;
    }
}
