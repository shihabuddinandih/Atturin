<?php

namespace App\Http\Controllers;

use App\Jobs\SendRegistrationConfirmationJob;
use App\Models\EventWaitlist;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WaitlistController extends Controller
{
    public function claim(Request $request, $token)
    {
        $entry = EventWaitlist::where('token', $token)->firstOrFail();

        if ($entry->status !== 'contacted') {
            return redirect()->route('player.join.show', $entry->event->slug)
                ->with('error', 'Tautan klaim tidak valid atau sudah digunakan.');
        }

        if ($entry->expires_at && $entry->expires_at->isPast()) {
            // mark expired and notify flow to promote next (job/cron)
            $entry->update(['status' => 'expired']);
            return redirect()->route('player.join.show', $entry->event->slug)
                ->with('error', 'Tautan klaim sudah kedaluwarsa.');
        }

        $event = $entry->event;

        // Ensure slot still available, then accept and route to payment flow
        $accepted = false;
        $response = null;

        DB::transaction(function () use ($entry, $event, &$accepted, &$response) {
            \App\Models\Event::whereKey($event->id)->lockForUpdate()->first();
            $joined = $event->occupiedSlotCount();
            if ($joined < $event->slot_max) {
                // Nominal dihitung ulang dari harga posisi yang dipilih saat masuk antrean (+ biaya admin).
                // Tidak memakai event_waitlists.payment_amount karena baris lama bernilai default 0.
                $role = $entry->role_name ? collect($event->roles ?? [])->firstWhere('name', $entry->role_name) : null;
                $basePrice = $role ? (float) ($role['price'] ?? 0) : (float) $event->iuran_per_pemain;
                $amount = \App\Support\PaymentFee::total($basePrice, $event->metode_pembayaran);
                $isFree = $amount <= 0;
                $payFirst = $event->requiresPaymentFirst() && !$isFree;
                $player = $entry->player;
                if (!$player) {
                    // Create a minimal player record using phone
                    $player = Player::firstOrCreate(
                        ['kontak' => $entry->phone],
                        ['nama' => 'Peserta']
                    );
                }

                $existingRegistration = $event->players()->where('players.id', $player->id)->first();

                $payload = [
                    'status_join' => $payFirst ? 'hold' : 'joined',
                    'hadir' => false,
                    'payment_method' => $event->metode_pembayaran,
                    'payment_amount' => $amount,
                    'role_name' => $entry->role_name,
                    'payment_status' => $isFree ? 'paid' : 'pending',
                    'payment_reference' => $isFree ? 'GRATIS-' . strtoupper(Str::random(8)) : null,
                    'payment_paid_at' => $isFree ? now() : null,
                    'payment_expires_at' => $payFirst ? now()->addMinutes(\App\Models\Event::HOLD_MINUTES) : null,
                    'payment_snap_token' => null,
                    'registration_token' => $existingRegistration && $existingRegistration->pivot->registration_token
                        ? $existingRegistration->pivot->registration_token
                        : Str::random(40),
                ];

                if ($existingRegistration) {
                    $event->players()->updateExistingPivot($player->id, $payload);
                } else {
                    $event->players()->attach($player->id, $payload);
                }

                $entry->update(['status' => 'accepted']);
                $accepted = true;

                $joinContext = [
                    'event_id' => $event->id,
                    'player_id' => $player->id,
                ];
                session(['join_context' => $joinContext]);

                if (!$payFirst && !$isFree) {
                    SendRegistrationConfirmationJob::dispatch($event->id, $player->id, $payload['registration_token']);
                }

                $pendingCookieName = 'pending_payment_' . $event->id;
                $response = redirect()->route('registration.show', $payload['registration_token'])
                    ->with('success', 'Slot waiting list Anda berhasil diklaim. Silakan selesaikan pembayaran untuk mengamankan tempat.')
                    ->withCookie(cookie($pendingCookieName, json_encode($joinContext), 3));
            }
        });

        if ($accepted && $response) {
            return $response;
        }

        return redirect()->route('player.join.show', $event->slug)
            ->with('error', 'Maaf, slot sudah terisi oleh peserta lain.');
    }
}
