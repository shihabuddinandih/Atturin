<?php

namespace App\Http\Controllers;

use App\Models\PlayerContactRequest;

class SuperAdminPlayerContactRequestController extends Controller
{
    public function index()
    {
        $requests = PlayerContactRequest::with(['event', 'player'])
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->get()
            ->groupBy('event_id');

        return view('superadmin.player-contact-requests.index', compact('requests'));
    }

    public function send(PlayerContactRequest $contactRequest)
    {
        if ($contactRequest->status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah diproses.');
        }

        $player = $contactRequest->player;
        $event = $contactRequest->event;

        $phoneNumber = $this->normalizePhoneNumber($player->kontak ?? null);
        if (!$phoneNumber) {
            return back()->with('error', 'Tidak ada nomor kontak yang valid untuk pemain ini.');
        }

        $playerName = trim($player->nama ?? 'Peserta');
        $eventName = $event->nama_event ?? 'event Anda';
        $message = "Halo {$playerName}, kami dari admin {$eventName} ingin menghubungi Anda terkait pendaftaran Anda. Ada yang bisa kami bantu?";

        $contactRequest->update([
            'status' => 'sent',
            'sent_by' => auth()->id(),
            'sent_at' => now(),
        ]);

        $whatsappUrl = 'https://wa.me/' . $phoneNumber . '?text=' . urlencode($message);

        return redirect()->away($whatsappUrl)->with('success', 'Chat WhatsApp telah dibuka untuk peserta ini.');
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
