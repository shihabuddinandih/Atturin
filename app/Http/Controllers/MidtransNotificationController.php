<?php

namespace App\Http\Controllers;

use App\Jobs\SendWaitlistOfferJob;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Penerima HTTP Notification (webhook) Midtrans.
 *
 * Midtrans memanggil endpoint ini setiap kali status transaksi berubah, sehingga pembayaran
 * tetap tercatat walaupun peserta menutup halaman setelah membayar.
 * Daftarkan URL-nya di Dashboard Midtrans → Settings → Payment → Notification URL:
 *   https://<domain>/midtrans/notification
 */
class MidtransNotificationController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $serverKey = (string) config('services.midtrans.server_key');
        if ($serverKey === '') {
            return response()->json(['message' => 'Midtrans belum dikonfigurasi.'], 503);
        }

        $orderId = (string) $request->input('order_id', '');
        $statusCode = (string) $request->input('status_code', '');
        $grossAmount = (string) $request->input('gross_amount', '');
        $signature = (string) $request->input('signature_key', '');

        // Verifikasi bahwa notifikasi benar-benar dari Midtrans
        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        if ($orderId === '' || !hash_equals($expected, $signature)) {
            Log::warning('Midtrans notification: signature tidak valid', ['order_id' => $orderId]);

            return response()->json(['message' => 'Signature tidak valid.'], 403);
        }

        $row = DB::table('event_player')->where('payment_reference', $orderId)->first();
        if (!$row) {
            // Bukan order pendaftaran event (atau order lama yang sudah diganti) — tetap balas 200 agar tidak di-retry
            return response()->json(['message' => 'Order tidak dikenal.']);
        }

        if ((int) round((float) $grossAmount) !== (int) round((float) $row->payment_amount)) {
            Log::warning('Midtrans notification: nominal tidak cocok', [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
                'payment_amount' => $row->payment_amount,
            ]);

            return response()->json(['message' => 'Nominal tidak cocok.'], 422);
        }

        $transactionStatus = (string) $request->input('transaction_status', 'pending');
        $fraudStatus = (string) $request->input('fraud_status', 'accept');

        $newStatus = match ($transactionStatus) {
            'settlement' => 'paid',
            'capture' => $fraudStatus === 'accept' ? 'paid' : 'pending',
            'deny', 'cancel', 'expire', 'failure' => 'failed',
            default => 'pending',
        };

        $this->apply($row, $newStatus);

        return response()->json(['status' => $newStatus]);
    }

    private function apply(object $row, string $newStatus): void
    {
        // Pembayaran yang sudah lunas tidak pernah diturunkan statusnya oleh notifikasi yang datang terlambat
        if ($row->payment_status === 'paid' || $newStatus === 'pending') {
            return;
        }

        $query = DB::table('event_player')->where('id', $row->id);

        if ($newStatus === 'paid') {
            $query->update([
                'status_join' => 'joined',
                'payment_status' => 'paid',
                'payment_paid_at' => now(),
                'payment_expires_at' => null,
                'payment_snap_token' => null,
                'updated_at' => now(),
            ]);

            if ($row->status_join === 'batal') {
                // Slot sempat dilepas sebelum pembayaran masuk; peserta tetap dimasukkan karena sudah membayar.
                Log::warning('Midtrans notification: pembayaran masuk setelah slot dilepas', [
                    'event_id' => $row->event_id,
                    'player_id' => $row->player_id,
                ]);
            }

            return;
        }

        // failed: hanya mode bayar dulu (hold) yang otomatis batal; mode bayar menyusul tetap terdaftar
        $payload = ['payment_status' => 'failed', 'payment_snap_token' => null, 'updated_at' => now()];
        if ($row->status_join === 'hold') {
            $payload['status_join'] = 'batal';
        }
        $query->update($payload);

        if ($row->status_join === 'hold') {
            $event = Event::find($row->event_id);
            if ($event && $event->enable_waiting_list) {
                $next = $event->waitlists()->where('status', 'waiting')->oldest()->first();
                if ($next) {
                    SendWaitlistOfferJob::dispatch($next);
                }
            }
        }
    }
}
