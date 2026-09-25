<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $tournament->nama_turnamen) &mdash; {{ config('app.name', 'Atturin') }}</title>
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
                        brand: { 500: '#0052FF', 600: '#0042CC', 700: '#003199', 900: '#0A1628' },
                        lime: { 400: '#ABD600', 500: '#9BC200' },
                        surface: '#F0F2F5',
                    },
                    fontFamily: { sans: ['Lexend', 'sans-serif'] },
                },
            },
        }
    </script>
    <style>
        body { font-family: 'Lexend', sans-serif; }
        .pro-card { background:#fff; border-radius:16px; border:1px solid #E5E7EB; box-shadow:0 1px 3px rgba(0,0,0,0.04); }
        .live-dot { animation: pulse 1.5s ease-in-out infinite; }
        @keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:0.4;} }
    </style>
    @stack('styles')
</head>
<body class="bg-surface min-h-screen">
    <nav class="sticky top-0 z-40 bg-[#0A1628] border-b border-slate-800 text-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14">
                <a href="{{ route('tournament.show', $tournament) }}" class="flex items-center">
                    <img src="{{ asset('images/Logo/Horizontal/Secondary.png') }}" class="h-6 object-contain" alt="{{ config('app.name', 'Atturin') }}">
                </a>
                <div class="flex items-center gap-6 text-sm font-medium text-slate-300">
                    <a href="{{ route('tournament.show', $tournament) }}"
                       class="pb-0.5 transition-colors {{ request()->routeIs('tournament.show') || request()->routeIs('tournament.match.show') ? 'text-lime-400 border-b-2 border-lime-400 font-semibold' : 'hover:text-white' }}">
                        Livescore
                    </a>
                    <a href="{{ route('tournament.bracket', $tournament) }}"
                       class="pb-0.5 transition-colors {{ request()->routeIs('tournament.bracket') ? 'text-lime-400 border-b-2 border-lime-400 font-semibold' : 'hover:text-white' }}">
                        Bracket
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
