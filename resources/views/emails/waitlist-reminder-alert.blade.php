<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reminder Waitlist</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #1f2937;">
    <div style="max-width: 640px; margin: 0 auto; padding: 24px;">
        <h2 style="margin-bottom: 12px;">Reminder waitlist perlu ditindaklanjuti</h2>
        <p>Super admin, ada pemain yang perlu di-reminder untuk event berikut:</p>

        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin: 16px 0;">
            <p style="margin: 4px 0;"><strong>Event:</strong> {{ $waitlist->event->nama_event ?? '-' }}</p>
            <p style="margin: 4px 0;"><strong>Pemain:</strong> {{ $playerName }}</p>
            <p style="margin: 4px 0;"><strong>Nominal:</strong> Rp {{ number_format($paymentAmount, 0, ',', '.') }}</p>
            <p style="margin: 4px 0;"><strong>Link pembayaran:</strong> <a href="{{ $paymentLink }}">{{ $paymentLink }}</a></p>
        </div>

        <p><strong>Template pesan:</strong></p>
        <div style="background: #fff7ed; border-left: 4px solid #f59e0b; padding: 12px; border-radius: 4px;">
            {{ $message }}
        </div>

        <p style="margin-top: 16px;">Silakan buka halaman reminder untuk menindaklanjuti peserta ini.</p>
    </div>
</body>
</html>
