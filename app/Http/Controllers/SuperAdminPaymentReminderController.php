<?php

namespace App\Http\Controllers;

use App\Mail\PaymentReminderAlertMail;
use App\Models\Event;
use App\Models\Player;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SuperAdminPaymentReminderController extends Controller
{
    public function index()
    {
        $events = Event::with(['players' => function ($query) {
            $query->wherePivot('payment_status', 'pending')
                ->orderBy('event_player.created_at', 'asc');
        }])
            ->whereHas('players', function ($query) {
                $query->where('event_player.payment_status', 'pending');
            })
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->get();

        return view('superadmin.payment-reminders.index', compact('events'));
    }

    public function remind(Event $event, Player $player)
    {
        if (!$event->players()->where('players.id', $player->id)->exists()) {
            return back()->with('error', 'Pemain tidak terdaftar pada event ini.');
        }

        $pivot = $event->players()->where('players.id', $player->id)->first()->pivot;

        if ($pivot->payment_status !== 'pending') {
            return back()->with('error', 'Status pembayaran tidak dalam keadaan tertunda.');
        }

        $phone = $player->kontak;
        $phoneNumber = $this->normalizePhoneNumber($phone);

        if (!$phoneNumber) {
            return back()->with('error', 'Tidak ada nomor kontak yang valid untuk pemain ini.');
        }

        $playerName = trim($player->nama ?? 'Peserta');
        $paymentAmount = $pivot->payment_amount > 0 ? $pivot->payment_amount : ($event->iuran_per_pemain ?? 0);
        $paymentLink = route('player.join.show', $event->slug);
        $message = "Halo {$playerName}, kami ingin mengingatkan bahwa pendaftaran Anda untuk event {$event->nama_event} telah dicatat. " .
            "Silakan segera selesaikan pembayaran sebesar Rp " . number_format($paymentAmount, 0, ',', '.') . " melalui halaman berikut: {$paymentLink} " .
            "Agar tempat Anda tetap terjaga.";

        $superAdmins = User::where('role', 'superadmin')->get();
        foreach ($superAdmins as $superAdmin) {
            if (!empty($superAdmin->email)) {
                try {
                    Mail::to($superAdmin->email)->send(new PaymentReminderAlertMail($event, $player, $playerName, $paymentAmount, $paymentLink, $message));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        $whatsappUrl = 'https://wa.me/' . $phoneNumber . '?text=' . urlencode($message);

        return redirect()->away($whatsappUrl)->with('success', 'Template reminder pembayaran telah siap dan dibuka di WhatsApp.');
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
