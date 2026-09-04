<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reminder Pembayaran — {{ config('app.name', 'Atturin') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/Logo/Logo (Lettermark)/Primary Dark.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#F0F5FF',
                            100: '#E1E9FF',
                            500: '#0052FF',
                            600: '#0042CC',
                            700: '#003199',
                            900: '#0A1628',
                        },
                        violet: {
                            50: '#F5F3FF',
                            100: '#EDE9FE',
                            500: '#8B5CF6',
                            600: '#7C3AED',
                            700: '#6D28D9',
                        },
                        surface: '#F8FAFC',
                    },
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                },
            },
        }
    </script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .sidebar { width: 280px; }
        .main-content { margin-left: 280px; }
        .sidebar-link {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 18px; border-radius: 12px;
            font-size: 15px; font-weight: 500; color: #94A3B8;
            transition: all 0.2s ease;
            position: relative;
        }
        .sidebar-link:hover { background: rgba(255,255,255,0.05); color: #fff; }
        .sidebar-link.active {
            background: rgba(255,255,255,0.1); color: #fff; font-weight: 600;
        }
        .sidebar-link.active::before {
            content: ''; position: absolute; left: -12px; top: 8px; bottom: 8px; width: 4px; border-radius: 0 4px 4px 0; background: #8B5CF6;
        }
        .pro-card { background: #fff; border-radius: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
    </style>
</head>
<body class="bg-surface text-slate-800 antialiased min-h-screen">
    <aside class="sidebar fixed top-0 left-0 bottom-0 bg-[#0A1628] border-r border-slate-800 flex flex-col z-40">
        <div class="px-7 pt-7 pb-5">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/Logo/Logo (Lettermark)/Secondary.png') }}" class="h-8 object-contain" alt="Logo">
                <div>
                    <h1 class="text-white text-base font-bold tracking-wide">Atturin HQ</h1>
                    <span class="text-violet-400 text-[10px] uppercase font-bold tracking-widest">Super Admin</span>
                </div>
            </div>
        </div>

        <nav class="flex-1 px-4 py-4 space-y-1">
            <a href="{{ route('superadmin.withdrawals.index') }}" class="sidebar-link {{ request()->routeIs('superadmin.withdrawals.index') ? 'active' : '' }}">
                <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Penarikan Dana (Withdraw)
            </a>
            <a href="{{ route('superadmin.registration-confirmations.index') }}" class="sidebar-link {{ request()->routeIs('superadmin.registration-confirmations.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                Konfirmasi Pendaftaran
            </a>
            <a href="{{ route('superadmin.waitlist-reminders.index') }}" class="sidebar-link {{ request()->routeIs('superadmin.waitlist-reminders.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10h.01M12 10h.01M16 10h.01M9 16h6M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z"/></svg>
                Reminder Waiting List
            </a>
            <a href="{{ route('superadmin.payment-reminders.index') }}" class="sidebar-link {{ request()->routeIs('superadmin.payment-reminders.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Reminder Pembayaran
            </a>
            <a href="{{ route('superadmin.player-contact-requests.index') }}" class="sidebar-link {{ request()->routeIs('superadmin.player-contact-requests.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                Permintaan Kontak Peserta
            </a>
        </nav>

        <div class="px-5 pb-7 pt-4 border-t border-slate-800">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl border border-slate-700 text-sm font-semibold text-slate-300 hover:border-rose-500 hover:text-rose-500 transition-colors">
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <div class="main-content min-h-screen p-8 lg:p-10 flex flex-col gap-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Reminder Pembayaran</h2>
                <p class="text-sm text-slate-500 mt-1">Kirim pengingat manual untuk peserta yang sudah mendaftar tetapi belum menyelesaikan pembayaran.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-sm">
                {{ session('error') }}
            </div>
        @endif

        @forelse($events as $event)
            <div class="pro-card overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">{{ $event->nama_event }}</h3>
                        <p class="text-sm text-slate-500 mt-1">{{ $event->tanggal ? $event->tanggal->format('d M Y') : '-' }} • {{ $event->tempat ?: '-' }}</p>
                    </div>
                    <div class="text-sm font-semibold text-violet-700 bg-violet-50 rounded-full px-3 py-1">
                        {{ $event->players->count() }} pendaftaran tertunda
                    </div>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($event->players as $player)
                        @php
                            $alreadyReminded = $player->pivot->payment_status !== 'pending';
                            $statusBadgeClass = $alreadyReminded
                                ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                : 'bg-amber-50 text-amber-700 border border-amber-200';
                            $statusText = $alreadyReminded ? 'Sudah diproses' : 'Belum diproses';
                        @endphp
                        <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-semibold text-slate-800">{{ $player->nama ?? 'Peserta' }}</p>
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $statusBadgeClass }}">
                                        {{ $statusText }}
                                    </span>
                                </div>
                                <p class="text-sm text-slate-500 mt-1">{{ $player->kontak ?: '-' }}</p>
                                <p class="text-xs text-slate-400 mt-1">Nominal: Rp {{ number_format($player->pivot->payment_amount ?? ($event->iuran_per_pemain ?? 0), 0, ',', '.') }}</p>
                            </div>
                            <a href="{{ route('superadmin.payment-reminders.remind', ['event' => $event, 'player' => $player]) }}"
                               class="inline-flex items-center justify-center rounded-xl {{ $alreadyReminded ? 'bg-slate-600 hover:bg-slate-700' : 'bg-green-600 hover:bg-green-700' }} text-white font-semibold px-4 py-2.5 text-sm shadow-sm">
                                {{ $alreadyReminded ? 'Kirim Ulang Reminder' : 'Reminder via WhatsApp' }}
                            </a>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-sm text-slate-500">
                            Tidak ada peserta dengan pembayaran tertunda untuk event ini.
                        </div>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="pro-card px-6 py-10 text-center text-sm text-slate-500">
                Belum ada event dengan pendaftaran tertunda.
            </div>
        @endforelse
    </div>
</body>
</html>
