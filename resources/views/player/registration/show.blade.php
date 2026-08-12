<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pendaftaran — {{ config('app.name', 'Atturin') }}</title>
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
                <!-- <div class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-300">
                    <a href="#" class="hover:text-white transition-colors">Events</a>
                    <a href="#" class="hover:text-white transition-colors">My Schedule</a>
                    <a href="#" class="hover:text-white transition-colors">Payments</a>
                </div> -->
            </div>
        </div>
    </nav>

    @php
        $status = $latestJoin ? $latestJoin->pivot->payment_status : 'pending';
        $statusLabel = strtoupper($status);

        $computeAdminFee = function (float $base, string $method) {
            if ($method !== 'online_banking') {
                return 0.0;
            }
            if ($base <= 49000) {
                return 1500.0;
            }
            if ($base <= 99000) {
                return 3000.0;
            }
            return (float) ($base * 0.03);
        };

        if ($latestJoin) {
            $paymentAmount = (float) $latestJoin->pivot->payment_amount;
        } else {
            $basePrice = (float) $event->iuran_per_pemain;
            $paymentAmount = $basePrice + $computeAdminFee($basePrice, $event->metode_pembayaran);
        }
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
                <p class="text-sm text-gray-500 mt-2">Bayar kapan saja sebelum event dimulai dengan tombol pembayaran di bawah.</p>
            </div>

            {{-- Send to own WhatsApp --}}
            <!-- @if($whatsappShareUrl)
                <a href="{{ $whatsappShareUrl }}" target="_blank" rel="noopener"
                    class="mt-5 w-full inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 hover:bg-emerald-100 transition-colors">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12.002 2C6.478 2 2 6.477 2 12c0 1.851.505 3.586 1.386 5.076L2 22l5.058-1.328A9.94 9.94 0 0012.002 22C17.525 22 22 17.523 22 12S17.525 2 12.002 2zm0 18.09c-1.66 0-3.207-.487-4.508-1.324l-.323-.204-3.003.788.802-2.928-.21-.301A8.076 8.076 0 013.91 12c0-4.463 3.63-8.09 8.092-8.09 4.462 0 8.09 3.627 8.09 8.09 0 4.462-3.628 8.09-8.09 8.09z"/></svg>
                    Kirim ke WhatsApp saya
                </a>
            @endif -->

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
                        Rp {{ number_format($paymentAmount, 0, ',', '.') }}
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

            {{-- Back / Cancel --}}
            <div class="mt-6 flex flex-col sm:flex-row sm:justify-center gap-3">
                <a href="{{ route('player.join.show', $event->slug) }}"
                    class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Halaman Pendaftaran
                </a>
                @if($latestJoin && $latestJoin->pivot->payment_status !== 'paid')
                    <button type="button" id="btn-cancel-reg"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-rose-500 px-4 py-3 text-sm font-semibold text-white hover:bg-rose-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Batalkan Pendaftaran
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Cancel Confirmation Modal --}}
    @if($latestJoin && $latestJoin->pivot->payment_status !== 'paid')
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
                Data pendaftaran Anda akan <span class="font-semibold text-rose-500">dihapus</span> dan Anda perlu mendaftar ulang jika ingin bergabung kembali.
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

    @if($latestJoin && $latestJoin->pivot->payment_method === 'online_banking' && $latestJoin->pivot->payment_status !== 'paid')
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
                        if (result.status === 'paid') {
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

                window.addEventListener('focus', redirectIfPaid);
                const pollInterval = setInterval(redirectIfPaid, 5000);

                window.addEventListener('beforeunload', () => {
                    pollingActive = false;
                    clearInterval(pollInterval);
                });
            })();
        </script>
    @endif
</body>
</html>
