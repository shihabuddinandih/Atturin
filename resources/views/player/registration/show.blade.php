<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pendaftaran — {{ config('app.name', 'Atturin') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/Logo/Logo (Lettermark)/Primary Dark.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50:'#E6EEFF', 100:'#CCDDFF', 500:'#0052FF', 600:'#0042CC', 700:'#003199', 900:'#0A1628' },
                        lime: { 400:'#ABD600', 500:'#9BC200' },
                        surface: '#F0F2F5',
                    },
                    fontFamily: { sans: ['Lexend','sans-serif'] },
                },
            },
        }
    </script>
    @php
        $snapScript = config('services.midtrans.is_production')
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    @endphp
    <script src="{{ $snapScript }}" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    <style>
        body { font-family: 'Lexend', sans-serif; }
        .pro-card { background:#fff; border-radius:16px; border:1px solid #E5E7EB; box-shadow:0 1px 3px rgba(0,0,0,0.04); }
        .check-pop { animation: checkPop 0.5s ease-out; }
        @keyframes checkPop { 0%{transform:scale(0);opacity:0;} 60%{transform:scale(1.15);} 100%{transform:scale(1);opacity:1;} }
        .fade-up { animation: fadeUp 0.4s ease-out; }
        @keyframes fadeUp { from{opacity:0;transform:translateY(12px);} to{opacity:1;transform:translateY(0);} }
        .ticket-notch { position:absolute; top:-12px; width:24px; height:24px; border-radius:9999px; background:#F0F2F5; }
    </style>
</head>
<body class="bg-surface min-h-screen">

    @php
        $status = $latestJoin ? $latestJoin->pivot->payment_status : 'pending';
        $computeAdminFee = fn (float $base, string $method) => \App\Support\PaymentFee::adminFee($base, $method);
        $paymentAmount = $latestJoin
            ? (float) $latestJoin->pivot->payment_amount
            : (float) $event->iuran_per_pemain + $computeAdminFee((float) $event->iuran_per_pemain, $event->metode_pembayaran);
        $rupiah = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');

        $joinStatus = $latestJoin?->pivot->status_join;
        $isHold = $joinStatus === 'hold';
        $isCancelled = $joinStatus === 'batal';
        $isPaid = $status === 'paid' && !$isCancelled;
        $isOnline = $latestJoin && $latestJoin->pivot->payment_method === 'online_banking';
        $roleName = $latestJoin?->pivot->role_name;
        $waktu = \Illuminate\Support\Str::substr((string) $event->waktu, 0, 5);
        $tanggalLabel = \Carbon\Carbon::parse($event->tanggal)->translatedFormat('l, d F Y');
        $registrationCode = strtoupper(substr($token, 0, 4) . '-' . substr($token, 4, 4));

        // Google Calendar (waktu event diisi dalam WIB)
        $start = \Carbon\Carbon::parse(\Carbon\Carbon::parse($event->tanggal)->format('Y-m-d') . ' ' . $waktu, 'Asia/Jakarta');
        $calendarUrl = 'https://calendar.google.com/calendar/render?' . http_build_query([
            'action' => 'TEMPLATE',
            'text' => $event->nama_event,
            'dates' => $start->copy()->utc()->format('Ymd\THis\Z') . '/' . $start->copy()->addHours(2)->utc()->format('Ymd\THis\Z'),
            'location' => $event->tempat,
            'details' => 'Status pendaftaran: ' . route('registration.show', $token),
        ]);
    @endphp

    {{-- Navbar --}}
    <nav class="bg-brand-900 border-b border-slate-800 text-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14">
                <img src="{{ asset('images/Logo/Horizontal/Secondary.png') }}" class="h-8 object-contain" alt="{{ config('app.name', 'Atturin') }}">
                <a href="{{ route('player.join.show', $event->slug) }}" class="text-xs font-semibold text-white/70 hover:text-white">Lihat Event</a>
            </div>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-10 fade-up">
        {{-- Flash --}}
        @if(session('success'))
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ session('error') }}</div>
        @endif
        @if(session('info'))
            <div class="mb-5 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700">{{ session('info') }}</div>
        @endif

        @if($isCancelled)
            {{-- ═══ Pendaftaran gugur ═══ --}}
            <div class="pro-card mx-auto max-w-lg p-6 sm:p-8 text-center">
                <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-rose-100">
                    <svg class="h-8 w-8 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Pendaftaran Batal</h1>
                <p class="mt-2 text-sm text-gray-500">Pendaftaran Anda di <span class="font-semibold text-gray-800">{{ $event->nama_event }}</span> tidak berlaku lagi dan slot sudah dilepas.</p>
                <p class="mt-1 text-sm text-gray-500">Silakan daftar ulang jika slot masih tersedia.</p>
                <a href="{{ route('player.join.show', $event->slug) }}" class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-3 text-sm font-bold text-white hover:bg-brand-600">Daftar Ulang</a>
            </div>

        @elseif($isPaid)
            {{-- ═══ Tiket: Anda terdaftar ═══ --}}
            <div class="text-center">
                <div class="check-pop mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-lime-400 shadow-lg shadow-lime-400/40">
                    <svg class="h-10 w-10 text-brand-900" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                </div>
                <span class="mt-5 inline-flex items-center gap-2 rounded-full bg-gray-200/70 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-gray-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-lime-500"></span>
                    {{ $paymentAmount > 0 ? 'Pendaftaran terkonfirmasi · Lunas' : 'Pendaftaran terkonfirmasi · Gratis' }}
                </span>
                <h1 class="mt-3 text-3xl sm:text-4xl font-bold text-gray-900">Anda Terdaftar!</h1>
                <p class="mx-auto mt-2 max-w-md text-sm text-gray-500">
                    @if($paymentAmount > 0)
                        Pembayaran <span class="font-bold text-gray-900">{{ $rupiah($paymentAmount) }}</span> berhasil.
                    @endif
                    Slot {{ $roleName ? 'posisi ' . $roleName : 'Anda' }} sudah resmi masuk roster.
                </p>
            </div>

            <div class="relative mx-auto mt-8 max-w-3xl overflow-hidden rounded-3xl bg-white shadow-xl ring-1 ring-gray-200">
                <div class="bg-brand-900 px-6 py-6 sm:px-8 text-white">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-lime-400">✓ Tiket resmi {{ config('app.name', 'Atturin') }}</span>
                            <h2 class="mt-3 text-2xl font-bold break-words">{{ $event->nama_event }}</h2>
                            <p class="mt-2 text-sm text-white/80">{{ $tanggalLabel }} · {{ $waktu }} WIB</p>
                            <p class="mt-1 text-sm text-white/60">{{ $event->tempat }}</p>
                        </div>
                        <span class="rounded-full bg-brand-500 px-3 py-1 text-[10px] font-bold uppercase tracking-wider">Event Komunitas</span>
                    </div>
                </div>
                <div class="relative border-t-2 border-dashed border-gray-200">
                    <span class="ticket-notch -left-3"></span>
                    <span class="ticket-notch -right-3"></span>
                </div>
                <dl class="grid grid-cols-1 gap-5 px-6 py-6 sm:grid-cols-2 sm:px-8">
                    <div>
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Peserta</dt>
                        <dd class="mt-1 text-lg font-bold text-gray-900 break-words">{{ $latestJoin->nama }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Posisi</dt>
                        <dd class="mt-1 text-lg font-bold text-gray-900">{{ $roleName ?: 'Peserta' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Kode Pendaftaran</dt>
                        <dd class="mt-1 flex items-center gap-2">
                            <span class="text-lg font-bold text-brand-600 tracking-wider" id="reg-code">{{ $registrationCode }}</span>
                            <button type="button" class="copy-btn rounded-md bg-gray-100 px-2 py-1 text-[11px] font-semibold text-gray-600 hover:bg-gray-200" data-copy="{{ $registrationCode }}">Salin</button>
                        </dd>
                        <p class="mt-0.5 text-[11px] text-gray-400">Tunjukkan ke penyelenggara saat hari-H</p>
                    </div>
                    <div>
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Status Pembayaran</dt>
                        <dd class="mt-1 text-lg font-bold text-lime-600">{{ $paymentAmount > 0 ? 'LUNAS ' . $rupiah($paymentAmount) : 'GRATIS' }}</dd>
                        @if($latestJoin->pivot->payment_reference)
                            <p class="mt-0.5 text-[11px] text-gray-400 break-all">Ref: {{ $latestJoin->pivot->payment_reference }}</p>
                        @endif
                    </div>
                </dl>
            </div>

            <section class="pro-card mx-auto mt-6 max-w-3xl p-5 sm:p-6">
                <h3 class="text-base font-bold text-gray-900">Persiapan Hari-H</h3>
                <p class="mb-4 text-xs text-gray-400">Hal yang perlu disiapkan sebelum bertanding</p>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    @php $arrive = $start->copy()->subMinutes(15)->format('H:i'); @endphp
                    @foreach([
                        ['Bawa sepatu olahraga dan botol minum pribadi.', '👟'],
                        ['Datang 15 menit lebih awal (' . $arrive . ' WIB) untuk pemanasan.', '⏱️'],
                        ['Tunjukkan halaman ini ke penyelenggara di lokasi.', '🎟️'],
                    ] as [$text, $emoji])
                        <div class="flex items-start gap-3 rounded-xl bg-gray-50 p-4 text-sm text-gray-700">
                            <span class="text-lg leading-none">{{ $emoji }}</span>
                            <span>{{ $text }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <div class="mx-auto mt-6 grid max-w-3xl grid-cols-1 gap-3 sm:grid-cols-2">
                <a href="{{ route('player.join.show', $event->slug) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-brand-500/25 hover:bg-brand-600">
                    Lihat Event & Roster
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <a href="{{ $calendarUrl }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3.5 text-sm font-bold text-gray-800 hover:bg-gray-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Tambah ke Google Calendar
                </a>
                @if($whatsappShareUrl)
                    <a href="{{ $whatsappShareUrl }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-3.5 text-sm font-bold text-white hover:bg-emerald-600 sm:col-span-2">
                        Simpan Tiket ke WhatsApp Saya
                    </a>
                @endif
            </div>

        @else
            {{-- ═══ Menunggu pembayaran (bayar dulu / bayar menyusul) ═══ --}}
            <div class="pro-card mb-6 flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-brand-500 text-sm font-bold text-white">2</span>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-brand-500">Langkah 2 dari 2</p>
                        <p class="text-base font-bold text-gray-900">Pembayaran & Verifikasi QRIS</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    @if($isHold)
                        <span class="inline-flex items-center gap-2 rounded-full bg-brand-900 px-4 py-2 text-xs font-bold text-lime-400">
                            <span class="h-2 w-2 animate-pulse rounded-full bg-lime-400"></span>
                            <span id="hold-label">Slot dikunci · sisa</span>
                            <span id="hold-countdown"
                                  data-expires-at="{{ \Carbon\Carbon::parse($latestJoin->pivot->payment_expires_at)->toIso8601String() }}"
                                  data-release-at="{{ \App\Models\Event::holdReleaseAt($latestJoin->pivot->payment_expires_at)->toIso8601String() }}">--:--</span>
                        </span>
                    @endif
                    @if($latestJoin && $status !== 'paid')
                        <button type="button" id="btn-cancel-reg" class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-500 hover:text-rose-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Batalkan Pendaftaran
                        </button>
                    @endif
                </div>
            </div>

            <span class="inline-flex items-center gap-2 rounded-full bg-brand-50 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-brand-600">Pembayaran resmi via Midtrans</span>
            <h1 class="mt-3 text-3xl sm:text-4xl font-bold text-gray-900">{{ $isHold ? 'Selesaikan Pembayaran' : 'Pendaftaran Tercatat' }}</h1>
            <p class="mt-2 max-w-2xl text-sm text-gray-500">
                @if($isHold)
                    Slot Anda sedang ditahan. Bayar dengan QRIS sebelum waktu habis; Anda baru masuk roster setelah pembayaran berhasil.
                @else
                    Anda sudah masuk roster. Lunasi sebelum event dimulai lewat tombol di bawah atau link pelunasan yang dikirim ke WhatsApp Anda.
                @endif
            </p>

            <div class="mt-6 grid grid-cols-1 gap-5 lg:grid-cols-2 lg:items-start">
                {{-- QRIS --}}
                <section class="pro-card p-5 sm:p-6">
                    <div class="flex items-center justify-between gap-2 rounded-xl bg-gray-50 px-4 py-3">
                        <span class="text-lg font-black tracking-tight text-rose-600">QRIS</span>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-brand-600">🔒 Dinamis · sekali pakai</span>
                    </div>

                    <div class="mt-5 rounded-2xl border border-gray-100 bg-gray-50/60 p-6 text-center">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Total yang harus dibayar</p>
                        <p class="mt-1 text-4xl font-bold text-gray-900">{{ $rupiah($paymentAmount) }}</p>
                        <p class="mt-1 text-xs font-semibold text-brand-500">Sudah termasuk biaya layanan</p>

                        @if($isOnline && !$isCancelled)
                            <button id="midtrans-pay-button" type="button" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-500 py-3.5 text-sm font-bold text-white shadow-lg shadow-brand-500/25 transition-all hover:bg-brand-600 disabled:opacity-60">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                Tampilkan QRIS & Bayar
                            </button>
                        @elseif($latestJoin && $latestJoin->pivot->payment_method === 'tunai')
                            <p class="mt-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">Pembayaran dilakukan tunai langsung ke penyelenggara sebelum atau saat event dimulai.</p>
                        @endif
                    </div>

                    <div class="mt-5 border-t border-gray-100 pt-4 text-center">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-brand-600">Bisa dibayar dengan aplikasi QRIS apa pun</p>
                        <div class="mt-3 flex flex-wrap justify-center gap-2">
                            @foreach(['GoPay', 'OVO', 'DANA', 'ShopeePay', 'BCA Mobile', "Livin' Mandiri"] as $app)
                                <span class="rounded-md bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-600">{{ $app }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-5 rounded-2xl bg-gray-50 p-5">
                        <p class="text-sm font-bold text-gray-900">3 Langkah Bayar</p>
                        <ol class="mt-3 space-y-3 text-sm text-gray-600">
                            @foreach([
                                'Klik <strong>Tampilkan QRIS & Bayar</strong>, lalu buka aplikasi e-wallet atau m-banking dan pilih <strong>Scan / QRIS</strong>.',
                                'Pindai kode QR yang muncul, atau simpan gambarnya lalu unggah dari galeri.',
                                'Pastikan nominal <strong>' . e($rupiah($paymentAmount)) . '</strong>, lalu konfirmasi dengan PIN Anda.',
                            ] as $i => $stepText)
                                <li class="flex gap-3">
                                    <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-brand-500 text-xs font-bold text-white">{{ $i + 1 }}</span>
                                    <span>{!! $stepText !!}</span>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </section>

                {{-- Ringkasan & status --}}
                <div class="space-y-4">
                    <section class="pro-card p-5 sm:p-6">
                        <div class="flex items-center justify-between gap-2 border-b border-gray-100 pb-3">
                            <h2 class="text-base font-bold text-gray-900">Ringkasan Pendaftaran</h2>
                            <span class="rounded-md bg-gray-100 px-2 py-0.5 font-mono text-[11px] font-bold text-gray-500">#{{ $registrationCode }}</span>
                        </div>
                        <div class="border-b border-gray-100 py-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Event</p>
                            <p class="mt-0.5 text-sm font-bold text-gray-900">{{ $event->nama_event }}</p>
                            <p class="text-xs text-gray-500">{{ $tanggalLabel }} · {{ $waktu }} WIB · {{ $event->tempat }}</p>
                        </div>
                        <dl class="divide-y divide-gray-100 text-sm">
                            <div class="flex justify-between gap-3 py-3"><dt class="text-gray-500">Nama peserta</dt><dd class="text-right font-semibold text-gray-900">{{ $latestJoin?->nama ?? '-' }}</dd></div>
                            <div class="flex justify-between gap-3 py-3"><dt class="text-gray-500">Posisi</dt><dd class="text-right font-semibold text-gray-900">{{ $roleName ?: 'Peserta' }}</dd></div>
                            <div class="flex justify-between gap-3 py-3"><dt class="text-gray-500">Metode</dt><dd class="text-right font-semibold text-brand-600">{{ $isOnline ? 'QRIS (Midtrans)' : 'Tunai' }}</dd></div>
                            <div class="flex items-end justify-between gap-3 py-3">
                                <dt><span class="block font-bold text-gray-900">Total</span><span class="text-[11px] text-gray-400">Termasuk biaya layanan</span></dt>
                                <dd class="text-2xl font-bold text-brand-600">{{ $rupiah($paymentAmount) }}</dd>
                            </div>
                        </dl>
                    </section>

                    @if($isOnline)
                        <section class="rounded-2xl bg-brand-900 p-5 sm:p-6 text-white shadow-xl">
                            <div class="flex items-start gap-3">
                                <span class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-full border-2 border-lime-400 text-lime-400">
                                    <svg class="h-5 w-5 animate-spin" style="animation-duration:3s" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </span>
                                <div>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-lime-400 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-brand-900"><span class="h-1.5 w-1.5 rounded-full bg-brand-900"></span>Status dipantau otomatis</span>
                                    <p class="mt-2 text-lg font-bold">{{ $status === 'failed' ? 'Pembayaran belum berhasil' : 'Menunggu pembayaran…' }}</p>
                                </div>
                            </div>
                            <p class="mt-3 text-sm text-white/70">
                                @if($status === 'failed')
                                    Transaksi sebelumnya gagal atau kedaluwarsa. Silakan buat QRIS baru dan bayar ulang.
                                @else
                                    Halaman ini mengecek status pembayaran setiap beberapa detik dan akan berpindah otomatis setelah lunas.
                                @endif
                            </p>
                            <div class="mt-4 grid grid-cols-1 gap-2 {{ app()->environment('local') ? 'sm:grid-cols-2' : '' }}">
                                <button type="button" id="btn-check-status" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white/10 px-4 py-2.5 text-sm font-bold hover:bg-white/20">Cek Status Sekarang</button>
                                @if(app()->environment('local'))
                                    {{-- Hanya muncul di lingkungan pengembangan --}}
                                    <form method="POST" action="{{ route('registration.simulatePayment', $token) }}">
                                        @csrf
                                        <button type="submit" class="w-full rounded-xl bg-lime-400 px-4 py-2.5 text-sm font-bold text-brand-900 hover:bg-lime-500">Simulasi Lunas (dev)</button>
                                    </form>
                                @endif
                            </div>
                        </section>
                    @endif

                    <div class="flex items-start gap-3 rounded-xl bg-white p-4 text-xs text-gray-500 ring-1 ring-gray-200">
                        <svg class="h-5 w-5 flex-shrink-0 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Pembayaran diproses Midtrans dan terverifikasi otomatis. Simpan link halaman ini untuk melihat status pendaftaran kapan saja.</span>
                    </div>
                </div>
            </div>
        @endif
    </main>

    <footer class="mt-10 border-t border-gray-200 bg-white">
        <div class="mx-auto flex max-w-5xl flex-col items-center justify-between gap-2 px-4 py-5 text-xs text-gray-400 sm:flex-row sm:px-6 lg:px-8">
            <p><span class="font-bold text-brand-900">ATTURIN</span> · Manajemen event olahraga komunitas</p>
            <p>© {{ now()->year }} {{ config('app.name', 'Atturin') }}</p>
        </div>
    </footer>

    <script>
        document.querySelectorAll('.copy-btn').forEach((btn) => btn.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(btn.dataset.copy);
                btn.textContent = 'Tersalin';
                setTimeout(() => { btn.textContent = 'Salin'; }, 1500);
            } catch (e) { /* clipboard tidak tersedia */ }
        }));
    </script>

    {{-- Cancel Confirmation Modal --}}
    @if($latestJoin && $latestJoin->pivot->payment_status !== 'paid' && !$isCancelled)
    <div id="modal-cancel" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div id="modal-backdrop" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 fade-up">
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-rose-100 mx-auto mb-4">
                <svg class="w-6 h-6 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
            <h2 class="text-lg font-bold text-gray-900 text-center">Batalkan Pendaftaran?</h2>
            <p class="text-sm text-gray-500 text-center mt-2">
                Data pendaftaran Anda akan <span class="font-semibold text-rose-500">dihapus</span> dan slot dilepas untuk peserta lain.
            </p>
            <div class="mt-6 flex gap-3">
                <button type="button" id="btn-cancel-no"
                    class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                    Tetap Daftar
                </button>
                <form action="{{ route('registration.cancel', $token) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit"
                        class="w-full py-2.5 rounded-xl bg-rose-500 hover:bg-rose-600 text-white text-sm font-bold transition-colors">
                        Ya, Batalkan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const modal = document.getElementById('modal-cancel');
            const btnOpen = document.getElementById('btn-cancel-reg');
            const btnClose = document.getElementById('btn-cancel-no');
            const backdrop = document.getElementById('modal-backdrop');

            function openModal() { modal.classList.remove('hidden'); modal.classList.add('flex'); }
            function closeModal() { modal.classList.add('hidden'); modal.classList.remove('flex'); }

            if (btnOpen) btnOpen.addEventListener('click', openModal);
            if (btnClose) btnClose.addEventListener('click', closeModal);
            if (backdrop) backdrop.addEventListener('click', closeModal);
        })();
    </script>
    @endif

    @if($latestJoin && $latestJoin->pivot->payment_method === 'online_banking' && $latestJoin->pivot->payment_status !== 'paid' && !$isCancelled)
        <script>
            (function () {
                const payButton = document.getElementById('midtrans-pay-button');
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const tokenUrl = @json(route('registration.midtrans.token', $token));
                const finishUrl = @json(route('registration.midtrans.finish', $token));
                const statusUrl = @json(route('registration.midtrans.status', $token));
                const registrationUrl = @json(route('registration.show', $token));

                async function postJson(url, payload) {
                    const response = await fetch(url, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(payload),
                    });

                    return response.json();
                }

                if (payButton) {
                    payButton.addEventListener('click', async () => {
                        payButton.disabled = true;

                        const tokenResult = await postJson(tokenUrl, {});
                        if (tokenResult.expired) {
                            window.location.reload();
                            return;
                        }
                        if (!tokenResult.token) {
                            payButton.disabled = false;
                            alert(tokenResult.message || 'Gagal membuat token pembayaran.');
                            return;
                        }

                        window.snap.pay(tokenResult.token, {
                            onSuccess: async (result) => {
                                await postJson(finishUrl, result);
                                pollingActive = false;
                                window.location.href = registrationUrl;
                            },
                            onPending: async (result) => {
                                await postJson(finishUrl, result);
                                pollingActive = false;
                                window.location.href = registrationUrl;
                            },
                            onError: async (result) => {
                                await postJson(finishUrl, result);
                                payButton.disabled = false;
                            },
                            onClose: async () => {
                                payButton.disabled = false;
                                await redirectIfPaid();
                            },
                        });
                    });
                }

                let pollingActive = true;

                async function checkPaymentStatus() {
                    if (!pollingActive) {
                        return false;
                    }
                    try {
                        const result = await postJson(statusUrl, {});
                        if (result.status === 'paid' || result.status === 'expired') {
                            pollingActive = false;
                            return true;
                        }
                    } catch (error) {
                        // ignore transient polling failures
                    }
                    return false;
                }

                async function redirectIfPaid() {
                    if (await checkPaymentStatus()) {
                        window.location.reload();
                    }
                }

                redirectIfPaid();

                const checkBtn = document.getElementById('btn-check-status');
                if (checkBtn) {
                    checkBtn.addEventListener('click', async () => {
                        checkBtn.disabled = true;
                        checkBtn.textContent = 'Memeriksa…';
                        pollingActive = true;
                        const done = await checkPaymentStatus();
                        if (done) { window.location.reload(); return; }
                        checkBtn.textContent = 'Belum ada pembayaran masuk';
                        setTimeout(() => { checkBtn.disabled = false; checkBtn.textContent = 'Cek Status Sekarang'; }, 2500);
                    });
                }

                window.addEventListener('focus', redirectIfPaid);
                const pollInterval = setInterval(redirectIfPaid, 5000);

                window.addEventListener('beforeunload', () => {
                    pollingActive = false;
                    clearInterval(pollInterval);
                });

                // Mode bayar dulu: hitung mundur masa tahan slot & buka QRIS otomatis
                const holdEl = document.getElementById('hold-countdown');
                if (holdEl) {
                    const deadline = new Date(holdEl.dataset.expiresAt).getTime();
                    const releaseAt = new Date(holdEl.dataset.releaseAt).getTime();
                    const tick = () => {
                        const left = Math.max(0, Math.floor((deadline - Date.now()) / 1000));
                        holdEl.textContent = String(Math.floor(left / 60)).padStart(2, '0') + ':' + String(left % 60).padStart(2, '0');
                        if (left <= 0) {
                            clearInterval(timer);
                            // Waktu bayar habis: polling tetap jalan untuk menangkap pembayaran detik terakhir,
                            // halaman dimuat ulang setelah slot benar-benar dilepas.
                            document.getElementById('hold-label').textContent = 'Waktu habis · memeriksa pembayaran…';
                            holdEl.textContent = '';
                            if (payButton) payButton.classList.add('hidden');
                            setTimeout(() => window.location.reload(), Math.max(0, releaseAt - Date.now()) + 3000);
                        }
                    };
                    const timer = setInterval(tick, 1000);
                    tick();
                    if (payButton && deadline > Date.now()) {
                        payButton.click();
                    }
                }
            })();
        </script>
    @endif
</body>
</html>
