<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Player;

class SuperAdminRegistrationConfirmationController extends Controller
{
    public function index()
    {
        $events = Event::with(['players' => function ($query) {
            $query->wherePivotNull('confirmation_sent_at')
                ->orderBy('event_player.created_at', 'asc');
        }])
            ->whereHas('players', function ($query) {
                $query->whereNull('event_player.confirmation_sent_at');
            })
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->get();

        return view('superadmin.registration-confirmations.index', compact('events'));
    }

    public function send(Event $event, Player $player)
    {
        if (!$event->players()->where('players.id', $player->id)->exists()) {
            return back()->with('error', 'Pemain tidak terdaftar pada event ini.');
        }

        $pivot = $event->players()->where('players.id', $player->id)->first()->pivot;

        $phoneNumber = $this->normalizePhoneNumber($player->kontak);
        if (!$phoneNumber) {
            return back()->with('error', 'Tidak ada nomor kontak yang valid untuk pemain ini.');
        }

        $playerName = trim($player->nama ?? 'Peserta');
        $registrationUrl = $pivot->registration_token
            ? route('registration.show', $pivot->registration_token)
            : route('player.join.show', $event->slug);

        $message = "Halo {$playerName}, pendaftaran Anda untuk event {$event->nama_event} sudah kami terima. " .
            "Anda dapat melihat status pendaftaran, melakukan pembayaran, atau membatalkan pendaftaran kapan saja melalui link berikut: {$registrationUrl}";

        $event->players()->updateExistingPivot($player->id, ['confirmation_sent_at' => now()]);

        $whatsappUrl = 'https://wa.me/' . $phoneNumber . '?text=' . urlencode($message);

        return redirect()->away($whatsappUrl)->with('success', 'Pesan konfirmasi pendaftaran telah disiapkan dan dibuka di WhatsApp.');
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
