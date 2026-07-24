<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Atturin') }} — Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('images/Logo/Logo (Lettermark)/Primary Dark.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#E6EEFF',
                            100: '#CCDDFF',
                            200: '#99BBFF',
                            300: '#6699FF',
                            400: '#3377FF',
                            500: '#0052FF',
                            600: '#0042CC',
                            700: '#003199',
                            800: '#002166',
                            900: '#0A1628',
                        },
                        lime: {
                            400: '#ABD600',
                            500: '#9BC200',
                        },
                        surface: '#F0F2F5',
                    },
                    fontFamily: {
                        sans: ['Lexend', 'sans-serif'],
                    },
                },
            },
        }
    </script>
    <style>
        body { font-family: 'Lexend', sans-serif; }

        .sidebar { width: 260px; }
        .main-content { margin-left: 260px; transition: margin-left 0.2s ease; }
        .sidebar-link {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 16px; border-radius: 10px;
            font-size: 14px; font-weight: 500; color: #94A3B8;
            transition: all 0.2s ease;
            position: relative;
        }
        .sidebar-link:hover { background: rgba(255, 255, 255, 0.05); color: #fff; }
        .sidebar-link.active {
            background: rgba(255, 255, 255, 0.1); color: #fff; font-weight: 600;
        }
        .sidebar-link.active::before {
            content: '';
            position: absolute; left: -12px; top: 6px; bottom: 6px;
            width: 4px; border-radius: 0 4px 4px 0;
            background: #ABD600;
        }

        .pro-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #E5E7EB;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .toast-enter { animation: slideInRight 0.35s ease-out; }
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(24px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 99px; }
        ::-webkit-scrollbar-track { background: transparent; }

        .sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.4); z-index: 40;
        }
        .sidebar-overlay.active { display: block; }

        /* When sidebar is open on mobile, lock body scroll */
        body.sidebar-open { overflow: hidden; }

        @media (max-width: 1023px) {
            .sidebar {
                position: fixed; left: -100% !important; top: 0; bottom: 0;
                z-index: 50; transition: left 0.28s ease, transform 0.28s ease;
                width: 86%; max-width: 340px; box-shadow: 0 10px 30px rgba(2,6,23,0.18);
            }
            .sidebar.open { left: 0 !important; }
            .main-content { margin-left: 0 !important; }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-surface text-gray-900 antialiased min-h-screen">
    <div id="sidebar-overlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <aside id="sidebar" class="sidebar fixed top-0 left-0 bottom-0 bg-[#0A1628] border-r border-slate-800 z-50 flex flex-col">
        <div class="px-6 pt-6 pb-4">
            <a href="{{ route('admin.dashboard') }}" class="block">
                <img src="{{ asset('images/Logo/Horizontal/Secondary.png') }}" class="h-8 object-contain" alt="{{ config('app.name', 'Atturin') }}">
            </a>
        </div>

        <nav class="flex-1 px-3 space-y-1 overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}"
               class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z"/></svg>
                Dashboard
            </a>
            <a href="{{ route('admin.events.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.events.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Events
            </a>
            <a href="{{ route('admin.members.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.members.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Members
            </a>
            <!-- <a href="{{ route('admin.finances.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.finances.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Finances
            </a> -->
            <a href="{{ route('admin.subscriptions.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 5h14M7 9h10M7 13h7M7 17h10M5 21h14"/></svg>
                Subscriptions
            </a>
            {{-- <button type="button" data-modal-target="notification-modal" class="sidebar-link">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                Notifications
                <span id="sidebar-notification-dot" class="ml-auto w-2 h-2 rounded-full bg-red-500 hidden"></span>
            </button>
            <button type="button" data-modal-target="faq-modal" class="sidebar-link">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                FAQ
            </button> --}}
            <a href="{{ route('admin.settings.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Settings
            </a>
        </nav>

        <div class="px-4 pb-5 pt-3">
            <a href="{{ route('admin.events.create') }}"
               class="flex items-center justify-center gap-2 w-full py-3 px-4 rounded-xl bg-gradient-to-r from-lime-400 to-lime-500 hover:from-lime-300 hover:to-lime-400 text-slate-950 font-extrabold text-sm transition-all duration-200 shadow-lg shadow-lime-500/15 hover:shadow-lime-500/30 hover:-translate-y-0.5 active:translate-y-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create Event
            </a>
        </div>
    </aside>

    <div class="main-content">
        <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-lg border-b border-gray-200">
            <div class="flex items-center justify-between h-16 px-6 lg:px-8">
                <button onclick="toggleSidebar()" class="lg:hidden mr-3 p-2 rounded-lg hover:bg-gray-100">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                <div class="flex-1 max-w-lg">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" placeholder="Cari event, member..."
                               class="w-full pl-10 pr-4 py-2 rounded-xl bg-gray-100 border-0 text-sm text-gray-700 placeholder-gray-400 focus:ring-2 focus:ring-brand-500/20 focus:bg-white transition-all">
                    </div>
                </div>

                <div class="flex items-center gap-3 ml-4">
                    <button type="button" data-modal-target="notification-modal" class="relative p-2 rounded-xl hover:bg-gray-100 transition-colors">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span id="topbar-notification-dot" class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full hidden"></span>
                    </button>
                    <button type="button" data-modal-target="faq-modal" class="p-2 rounded-xl hover:bg-gray-100 transition-colors">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </button>
                    <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:border-rose-200 hover:text-rose-600 transition-colors">
                            Logout
                        </button>
                    </form>

                </div>
            </div>
        </header>

        <main class="p-6 lg:p-8">
            @if(session('success'))
                <div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl toast-enter" role="alert">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl toast-enter" role="alert">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <div id="notification-modal" class="hidden fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black/40" data-modal-close="notification-modal"></div>
        <div class="absolute inset-x-0 top-16 mx-auto max-w-2xl bg-white rounded-2xl shadow-xl p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
                <div class="flex items-center gap-3">
                    <button id="mark-notifications-read" type="button" class="text-sm font-semibold text-brand-500 hover:text-brand-600">Mark all read</button>
                    <button type="button" class="text-gray-400 hover:text-gray-600" data-modal-close="notification-modal">Close</button>
                </div>
            </div>
            <div class="mt-4 space-y-3">
                <div class="border border-gray-100 rounded-xl p-4">
                    <p class="text-sm font-semibold text-gray-800">Pembayaran diterima</p>
                    <p class="text-sm text-gray-500">Bintang melunasi iuran Liga Jumat Malam.</p>
                    <p class="text-xs text-gray-400 mt-1">5 menit lalu</p>
                </div>
                <div class="border border-gray-100 rounded-xl p-4">
                    <p class="text-sm font-semibold text-gray-800">Slot hampir penuh</p>
                    <p class="text-sm text-gray-500">Mini Cup U23 tinggal 2 slot tersisa.</p>
                    <p class="text-xs text-gray-400 mt-1">30 menit lalu</p>
                </div>
                <div class="border border-gray-100 rounded-xl p-4">
                    <p class="text-sm font-semibold text-gray-800">Event baru dipublish</p>
                    <p class="text-sm text-gray-500">Friendly Match Sunday sudah tayang.</p>
                    <p class="text-xs text-gray-400 mt-1">2 jam lalu</p>
                </div>
            </div>
        </div>
    </div>

    <div id="faq-modal" class="hidden fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black/40" data-modal-close="faq-modal"></div>
        <div class="absolute inset-x-0 top-16 mx-auto max-w-2xl bg-white rounded-2xl shadow-xl p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">FAQ</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" data-modal-close="faq-modal">Close</button>
            </div>
            <div class="mt-4 space-y-3">
                <details class="border border-gray-100 rounded-xl p-4">
                    <summary class="text-sm font-semibold text-gray-800 cursor-pointer">Bagaimana cara membuat event baru?</summary>
                    <p class="text-sm text-gray-500 mt-2">Klik tombol Create Event di sidebar, isi detail event, lalu simpan.</p>
                </details>
                <details class="border border-gray-100 rounded-xl p-4">
                    <summary class="text-sm font-semibold text-gray-800 cursor-pointer">Bagaimana update pembayaran peserta?</summary>
                    <p class="text-sm text-gray-500 mt-2">Masuk ke detail event, pilih peserta, lalu update status pembayaran.</p>
                </details>
                <details class="border border-gray-100 rounded-xl p-4">
                    <summary class="text-sm font-semibold text-gray-800 cursor-pointer">Kenapa slot tidak bertambah?</summary>
                    <p class="text-sm text-gray-500 mt-2">Periksa slot maksimum di event dan pastikan status event aktif.</p>
                </details>
            </div>
        </div>
    </div>

    <script>
        function setNotificationCount(count) {
            const topDot = document.getElementById('topbar-notification-dot');
            const sideDot = document.getElementById('sidebar-notification-dot');
            const isActive = count > 0;

            if (topDot) {
                topDot.classList.toggle('hidden', !isActive);
            }

            if (sideDot) {
                sideDot.classList.toggle('hidden', !isActive);
            }

            localStorage.setItem('adminNotificationCount', String(count));
        }

        function closeModal(targetId) {
            const modal = document.getElementById(targetId);
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function openModal(targetId) {
            const modal = document.getElementById(targetId);
            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            const willOpen = !sidebar.classList.contains('open');
            sidebar.classList.toggle('open', willOpen);
            overlay.classList.toggle('active', willOpen);
            document.body.classList.toggle('sidebar-open', willOpen);
        }

        // Close sidebar when viewport becomes large again
        window.addEventListener('resize', () => {
            if (window.innerWidth > 1023) {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebar-overlay');
                if (sidebar.classList.contains('open')) {
                    sidebar.classList.remove('open');
                    overlay.classList.remove('active');
                    document.body.classList.remove('sidebar-open');
                }
            }
        });

        document.querySelectorAll('[data-modal-target]').forEach((trigger) => {
            trigger.addEventListener('click', () => {
                openModal(trigger.getAttribute('data-modal-target'));
            });
        });

        document.querySelectorAll('[data-modal-close]').forEach((button) => {
            button.addEventListener('click', () => {
                closeModal(button.getAttribute('data-modal-close'));
            });
        });

        const savedCount = Number(localStorage.getItem('adminNotificationCount') || 2);
        setNotificationCount(savedCount);

        const markReadButton = document.getElementById('mark-notifications-read');
        if (markReadButton) {
            markReadButton.addEventListener('click', () => {
                setNotificationCount(0);
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
