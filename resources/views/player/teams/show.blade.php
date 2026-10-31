<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembagian Tim — {{ $event->nama_event }} — {{ config('app.name', 'Atturin') }}</title>
    <meta name="description" content="Pembagian tim dan jadwal pertandingan {{ $event->nama_event }} di {{ $event->tempat }}.">
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
        .fade-up { animation: fadeUp 0.4s ease-out; }
        @keyframes fadeUp { from{opacity:0;transform:translateY(12px);} to{opacity:1;transform:translateY(0);} }
    </style>
</head>
<body class="bg-surface min-h-screen">

    {{-- Navbar --}}
    <nav class="sticky top-0 z-40 bg-[#0A1628] border-b border-slate-800 text-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-14">
                <img src="{{ asset('images/Logo/Horizontal/Secondary.png') }}" class="h-8 object-contain" alt="{{ config('app.name', 'Atturin') }}">
                <a href="{{ route('player.join.show', $event->slug) }}" class="text-xs font-semibold text-slate-300 hover:text-white transition-colors">Detail Event</a>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 lg:py-8 space-y-6 fade-up">
        {{-- Header --}}
        <div class="rounded-2xl bg-[#0A1628] text-white p-5 sm:p-6">
            <p class="text-[11px] font-bold uppercase tracking-wider text-lime-400">Pembagian Tim</p>
            <h1 class="text-xl sm:text-2xl font-bold mt-1">{{ $event->nama_event }}</h1>
            <div class="flex flex-wrap gap-x-5 gap-y-1 mt-3 text-sm text-slate-300">
                <span>{{ \Carbon\Carbon::parse($event->tanggal)->translatedFormat('d M Y') }} · {{ substr((string) $event->waktu, 0, 5) }}</span>
                <span>{{ $event->tempat }}</span>
                <span>{{ $event->teams->count() }} tim · {{ $event->matches->count() }} laga</span>
            </div>
        </div>

        {{-- Teams --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($event->teams as $team)
                <div class="pro-card overflow-hidden">
                    <div class="px-5 py-3 flex items-center justify-between" style="border-top: 4px solid {{ $team->warna }}">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full" style="background: {{ $team->warna }}"></span>
                            <h2 class="text-sm font-bold text-gray-900">{{ $team->nama_tim }}</h2>
                        </div>
                        <span class="text-xs text-gray-400">{{ $team->members->count() }} pemain</span>
                    </div>
                    <ul class="divide-y divide-gray-50">
                        @foreach($team->members as $member)
                            <li class="px-5 py-2 flex items-center justify-between gap-2 text-sm">
                                <span class="text-gray-800 truncate">{{ $member->player?->nama ?? '—' }}</span>
                                @if($member->role_name)
                                    <span class="px-2 py-0.5 rounded-md bg-gray-100 text-gray-600 text-[11px] font-medium flex-shrink-0">{{ $member->role_name }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        {{-- Schedule --}}
        @if($event->matches->isNotEmpty())
            <div class="pro-card overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">Jadwal Pertandingan</h2>
                </div>
                <ul class="divide-y divide-gray-50">
                    @foreach($event->matches as $match)
                        <li class="px-5 py-3 flex items-center gap-3">
                            <div class="w-14 flex-shrink-0 text-center">
                                <p class="text-[10px] uppercase tracking-wider text-gray-400 font-semibold">Laga {{ $match->urutan }}</p>
                                @if($match->perkiraan_mulai)
                                    <p class="text-xs font-bold text-brand-500">{{ substr($match->perkiraan_mulai, 0, 5) }}</p>
                                @endif
                            </div>
                            <div class="flex-1 grid grid-cols-[1fr_auto_1fr] items-center gap-2 text-sm font-semibold text-gray-900">
                                <span class="flex items-center justify-end gap-2 text-right truncate">
                                    {{ $match->homeTeam->nama_tim }}
                                    <span class="w-3 h-3 rounded-full flex-shrink-0" style="background: {{ $match->homeTeam->warna }}"></span>
                                </span>
                                <span class="px-2 py-0.5 rounded-md bg-surface text-[11px] font-bold text-gray-500">VS</span>
                                <span class="flex items-center gap-2 truncate">
                                    <span class="w-3 h-3 rounded-full flex-shrink-0" style="background: {{ $match->awayTeam->warna }}"></span>
                                    {{ $match->awayTeam->nama_tim }}
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</body>
</html>
