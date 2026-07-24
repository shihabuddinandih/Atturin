<?php

namespace App\Http\Controllers;

use App\Mail\WaitlistReminderAlertMail;
use App\Models\Event;
use App\Models\EventWaitlist;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SuperAdminWaitlistReminderController extends Controller
{
    public function index()
    {
        $events = Event::with(['waitlists' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }])
            ->whereHas('waitlists')
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->get();

        return view('superadmin.waitlist-reminders.index', compact('events'));
    }

    public function remind(EventWaitlist $waitlist)
    {
        $phone = $waitlist->phone ?: optional($waitlist->player)->kontak;
        $phoneNumber = $this->normalizePhoneNumber($phone);

        if (!$phoneNumber) {
            return back()->with('error', 'Tidak ada nomor kontak yang valid untuk pemain ini.');
        }

        $event = $waitlist->event;
        $playerName = trim(optional($waitlist->player)->nama ?? 'Peserta');
        $paymentAmount = $waitlist->payment_amount > 0 ? $waitlist->payment_amount : ($event->iuran_per_pemain ?? 0);
        $token = $waitlist->token ?: Str::random(48);

        $waitlist->update([
            'status' => 'contacted',
            'token' => $token,
            'contacted_at' => now(),
            'expires_at' => now()->addHours(24),
        ]);

        $paymentLink = route('waitlist.claim', $token);
        $message = "Halo {$playerName}, kami ingin mengingatkan bahwa Anda masih berada di waiting list untuk event {$event->nama_event}. " .
            "Silakan segera lanjutkan pelunasan sebesar Rp " . number_format($paymentAmount, 0, ',', '.') . " melalui tautan berikut: {$paymentLink}";

        $superAdmins = User::where('role', 'superadmin')->get();
        foreach ($superAdmins as $superAdmin) {
            if (!empty($superAdmin->email)) {
                try {
                    Mail::to($superAdmin->email)->send(new WaitlistReminderAlertMail($waitlist, $playerName, $paymentAmount, $paymentLink, $message));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        $whatsappUrl = 'https://wa.me/' . $phoneNumber . '?text=' . urlencode($message);

        return redirect()->away($whatsappUrl)->with('success', 'Template reminder telah siap dan dibuka di WhatsApp.');
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
