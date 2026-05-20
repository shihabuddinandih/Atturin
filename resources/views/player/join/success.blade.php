<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Berhasil — {{ config('app.name', 'Atturin') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/Logo/Logo (Lettermark)/Primary Dark.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50:'#E6EEFF', 500:'#0052FF', 600:'#0042CC', 700:'#003199', 900:'#0A1628' },
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
    </style>
</head>
<body class="bg-surface min-h-screen">

    {{-- Navbar --}}
    <nav class="bg-[#0A1628] border-b border-slate-800 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14">
                <img src="{{ asset('images/Logo/Horizontal/Secondary.png') }}" class="h-8 object-contain" alt="{{ config('app.name', 'Atturin') }}">
                <div class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-300">
                    <a href="#" class="hover:text-white transition-colors">Events</a>
                    <a href="#" class="hover:text-white transition-colors">My Schedule</a>
                    <a href="#" class="hover:text-white transition-colors">Payments</a>
                </div>
            </div>
        </div>
    </nav>

    @php
        $status = $latestJoin ? $latestJoin->pivot->payment_status : 'pending';
        $statusLabel = strtoupper($status);
    @endphp

    <div class="max-w-xl mx-auto px-4 py-10 fade-up">
        <div class="pro-card p-6 sm:p-8">
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            {{-- Success Icon --}}
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-emerald-100 mb-5 check-pop">
                    <svg class="h-8 w-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Registrasi Berhasil!</h1>
                <p class="text-sm text-gray-500 mt-2">Anda sudah tercatat di event <span class="font-semibold text-gray-800">{{ $event->nama_event }}</span>.</p>
            </div>

            {{-- Event Details --}}
            <div class="mt-6 rounded-xl border border-gray-200 bg-gray-50 p-5 space-y-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Jadwal
                    </span>
                    <span class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($event->tanggal)->format('d M Y') }}, {{ $event->waktu }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        Lokasi
                    </span>
                    <span class="font-semibold text-gray-800">{{ $event->tempat }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1"/></svg>
                        Iuran Anda
                    </span>
                    <span class="font-bold text-brand-500">
                        Rp {{ number_format((float) ($latestJoin ? $latestJoin->pivot->payment_amount : $event->iuran_per_pemain), 0, ',', '.') }}
                    </span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Metode</span>
                    <span class="font-semibold text-gray-800">{{ $event->metode_pembayaran === 'online_banking' ? 'Online Banking' : 'Tunai' }}</span>
                </div>
            </div>

            {{-- Payment Status --}}
            @if($latestJoin)
                <div class="mt-4 rounded-xl border border-brand-100 bg-brand-50 p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-gray-800">Status Pembayaran</span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                            {{ $status === 'paid' ? 'bg-emerald-100 text-emerald-700' : ($status === 'failed' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                            {{ $statusLabel }}
                        </span>
                    </div>
                    @if($latestJoin->pivot->payment_reference)
                        <p class="mt-2 text-xs text-gray-400">Ref: {{ $latestJoin->pivot->payment_reference }}</p>
                    @endif
                </div>

                @if($latestJoin->pivot->payment_method === 'online_banking' && $latestJoin->pivot->payment_status !== 'paid')
                    <button id="midtrans-pay-button" type="button" class="mt-5 w-full py-3 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm transition-all shadow-lg shadow-brand-500/20 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        Bayar via Midtrans
                    </button>
                @elseif($latestJoin->pivot->payment_method === 'tunai')
                    <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 flex items-start gap-2">
                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Pembayaran dilakukan tunai langsung ke admin sebelum atau saat match dimulai.
                    </div>
                @endif
            @endif

            {{-- Back Link --}}
            <div class="mt-6 text-center">
                <a href="{{ route('player.join.show', $event->slug) }}" class="inline-flex items-center gap-1.5 text-brand-500 hover:text-brand-600 font-semibold text-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Halaman Event
                </a>
            </div>
        </div>
    </div>

    @if($latestJoin && $latestJoin->pivot->payment_method === 'online_banking' && $latestJoin->pivot->payment_status !== 'paid')
        <script>
            (function () {
                const payButton = document.getElementById('midtrans-pay-button');
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const tokenUrl = @json(route('player.join.midtrans.token', $event->slug));
                const finishUrl = @json(route('player.join.midtrans.finish', $event->slug));

                async function postJson(url, payload) {
                    const response = await fetch(url, {
                        method: 'POST',
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
                        if (!tokenResult.token) {
                            payButton.disabled = false;
                            alert(tokenResult.message || 'Gagal membuat token pembayaran.');
                            return;
                        }

                        window.snap.pay(tokenResult.token, {
                            onSuccess: async (result) => {
                                await postJson(finishUrl, result);
                                window.location.reload();
                            },
                            onPending: async (result) => {
                                await postJson(finishUrl, result);
                                window.location.reload();
                            },
                            onError: async (result) => {
                                await postJson(finishUrl, result);
                                payButton.disabled = false;
                            },
                            onClose: () => {
                                payButton.disabled = false;
                            },
                        });
                    });
                }
            })();
        </script>
    @endif
</body>
</html>

