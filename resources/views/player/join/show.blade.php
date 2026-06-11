<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->nama_event }} — {{ config('app.name', 'Atturin') }}</title>
    <meta name="description" content="Gabung {{ $event->nama_event }} di {{ $event->tempat }}. Daftar sekarang di {{ config('app.name', 'Atturin') }}.">
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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14">
                <img src="{{ asset('images/Logo/Horizontal/Secondary.png') }}" class="h-8 object-contain" alt="{{ config('app.name', 'Atturin') }}">
                <div class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-300">
                    <a href="#" class="text-lime-400 border-b-2 border-lime-400 pb-0.5 font-semibold">Events</a>
                    <a href="#" class="hover:text-white transition-colors">My Schedule</a>
                    <a href="#" class="hover:text-white transition-colors">Teams</a>
                    <a href="#" class="hover:text-white transition-colors">Payments</a>
                </div>
                <div class="flex items-center gap-3">
                    <button class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-gradient-to-r from-lime-400 to-lime-500 hover:from-lime-300 hover:to-lime-400 text-slate-950 text-xs font-bold transition-all shadow-md shadow-lime-500/15">Create Event</button>
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white text-xs font-bold">P</div>
                </div>
            </div>
        </div>
    </nav>

    @php
        $slotPercentage = $event->slot_max > 0 ? min(100, (int) round(($joinedCount / $event->slot_max) * 100)) : 0;
        $paymentLabel = $event->metode_pembayaran === 'online_banking' ? 'QRIS' : 'Tunai ke Admin';
        $slotsLeft = $event->slot_max - $joinedCount;
        $isCustomEvent = $event->skema_iuran === 'custom';
        $rolesWithAvailability = $rolesWithAvailability ?? [];
        $customRoles = count($rolesWithAvailability) ? $rolesWithAvailability : ($event->roles ?? []);
        // prefer display_price when provided by controller
        if ($isCustomEvent && count($customRoles) > 0) {
            $displayPrices = array_filter(array_map(fn($r) => $r['display_price'] ?? null, $customRoles));
            if (!empty($displayPrices)) {
                $displayPrice = min($displayPrices);
            } else {
                $basePrice = min(array_column($customRoles, 'price'));
                $displayPrice = $event->metode_pembayaran === 'online_banking' ? (int) round($basePrice * 1.03) : $basePrice;
            }
        } else {
            $displayPrice = $event->metode_pembayaran === 'online_banking' ? (int) round((float)$event->iuran_per_pemain * 1.03) : (float)$event->iuran_per_pemain;
        }
        $adminNote = $event->metode_pembayaran === 'online_banking' ? 'sudah termasuk biaya admin dan sistem' : null;
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8 fade-up">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left Column: Event Details --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Hero Image --}}
                <div class="pro-card overflow-hidden relative">
                    <div class="h-56 sm:h-72 bg-gradient-to-br from-brand-900 via-brand-700 to-brand-500 flex items-center justify-center relative overflow-hidden">
                        <div class="absolute inset-0 opacity-10">
                            <div class="absolute top-10 left-10 w-40 h-40 border-2 border-white rounded-full"></div>
                            <div class="absolute bottom-5 right-10 w-64 h-64 border-2 border-white rounded-full"></div>
                            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-32 h-32 border border-white rounded-full"></div>
                        </div>
                        <div class="text-center relative z-10">
                            <p class="text-white/60 text-xs uppercase tracking-widest font-semibold mb-2">Community Event</p>
                            <h2 class="text-white text-2xl sm:text-3xl font-bold">{{ $event->nama_event }}</h2>
                        </div>
                        <span class="absolute top-4 left-4 px-3 py-1 rounded-lg text-xs font-bold {{ $isFull ? 'bg-rose-500 text-white' : 'bg-lime-400 text-brand-900' }}">
                            {{ $isFull ? '✕ Full' : '⚡ Terbuka' }}
                        </span>
                    </div>
                </div>

                {{-- Tags --}}
                <div class="flex flex-wrap gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-medium border border-gray-200 text-gray-600">Sport</span>
                    <span class="px-3 py-1 rounded-full text-xs font-medium border border-gray-200 text-gray-600">{{ $paymentLabel }}</span>
                    <span class="px-3 py-1 rounded-full text-xs font-medium border border-gray-200 text-gray-600">{{ $event->slot_max }} Slot</span>
                </div>

                {{-- Date --}}
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ \Carbon\Carbon::parse($event->tanggal)->translatedFormat('l, d M Y') }} • {{ $event->waktu }} WIB
                </div>

                {{-- Location + Info Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="pro-card p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            <h4 class="text-sm font-semibold text-gray-900">Lokasi</h4>
                        </div>
                        <p class="text-sm font-semibold text-gray-800">{{ $event->tempat }}</p>
                        <div class="mt-3 h-28 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center">
                            <svg class="w-8 h-8 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        </div>
                    </div>

                    <div class="pro-card p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <h4 class="text-sm font-semibold text-gray-900">Informasi</h4>
                        </div>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between flex-wrap gap-1">
                                <span class="text-gray-500">Biaya per slot</span>
                                @if($isCustomEvent)
                                    <div class="text-right">
                                        <span class="font-bold text-brand-500 block">Mulai dari Rp {{ number_format($displayPrice, 0, ',', '.') }}</span>
                                        <span class="text-[9px] text-amber-500 font-medium block mt-0.5">⚡ Harga tergantung role yang dipilih, {{ $adminNote }}</span>
                                    </div>
                                @elseif($event->skema_iuran === 'loyalitas')
                                    @php
                                        $baseEst = ($event->slot_max > 0) ? ($event->biaya_total_event / $event->slot_max) : 0;
                                        if ($event->metode_pembayaran === 'online_banking') {
                                            $minEst = round($baseEst * 0.70 * 1.03, -2);
                                            $maxEst = round($baseEst * 1.03, -2);
                                        } else {
                                            $minEst = round($baseEst * 0.70, -2);
                                            $maxEst = round($baseEst, -2);
                                        }
                                    @endphp
                                    <div class="text-right">
                                        <span class="font-bold text-brand-500 block">Rp {{ number_format($minEst, 0, ',', '.') }} - Rp {{ number_format($maxEst, 0, ',', '.') }}</span>
                                        <span class="text-[9px] text-amber-500 font-medium block mt-0.5">⚡ Berdasarkan keaktifan Anda{{ $event->metode_pembayaran === 'online_banking' ? ', sudah termasuk admin 3%' : '' }}</span>
                                    </div>
                                @else
                                    <span class="font-bold text-brand-500">Rp {{ number_format($displayPrice, 0, ',', '.') }}</span>
                                    @if($adminNote)
                                        <span class="text-[9px] text-amber-500 font-medium block mt-0.5">⚡ {{ $adminNote }}</span>
                                    @endif
                                @endif
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Pembayaran</span>
                                <span class="font-semibold text-gray-800">{{ $paymentLabel }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Kapasitas</span>
                                <span class="font-semibold text-gray-800">{{ $event->slot_max }} pemain</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($isCustomEvent)
                    <div class="pro-card p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">Role Tersedia</h3>
                                <p class="text-sm text-gray-500">Pilih role yang sesuai saat mendaftar.</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">Custom Pricing</span>
                        </div>
                        <div class="space-y-3">
                            @foreach($customRoles as $role)
                                @php
                                    $rolePrice = (float) ($role['price'] ?? 0);
                                    $roleDisplayPrice = $role['display_price'] ?? ($event->metode_pembayaran === 'online_banking' ? (int) round($rolePrice * 1.03) : $rolePrice);
                                    $roleAdminFee = $role['admin_fee'] ?? ($event->metode_pembayaran === 'online_banking' ? (int) round($rolePrice * 0.03) : 0);
                                @endphp
                                <div class="rounded-3xl border border-gray-200 bg-gray-50 p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ $role['name'] }}</p>
                                            <p class="text-xs text-gray-500">
                                                Slot maksimal {{ $role['slots'] }} · Terisi {{ $role['joined'] ?? 0 }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-semibold text-brand-600">Rp {{ number_format($roleDisplayPrice, 0, ',', '.') }}</p>
                                            <p class="text-[11px] text-slate-500">
                                                @if($role['is_full'])
                                                    <span class="text-rose-500">Penuh</span>
                                                @else
                                                    Sisa {{ $role['slots_left'] }} slot
                                                @endif
                                                @if($event->metode_pembayaran === 'online_banking')
                                                    <br><span class="text-[10px] text-amber-500">sudah termasuk biaya admin dan sistem</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Registration Form --}}
                @if(!$isFull)
                <div class="pro-card p-6 lg:hidden">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Isi Data Pemain</h3>
                    <p class="text-sm text-gray-500 mb-5">Masukkan nama dan kontak aktif untuk verifikasi.</p>

                    @if(session('error'))
                        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('player.join.store', $event->slug) }}" method="POST" class="space-y-4">
                        @csrf
                        @if($isCustomEvent)
                            <div>
                                <label for="role_mobile" class="block text-sm font-medium text-gray-700 mb-1.5">Pilih Role</label>
                                <select id="role_mobile" name="role_name" required
                                        class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 bg-white">
                                    <option value="">Pilih role...</option>
                                            @foreach($customRoles as $role)
                                                @php
                                                    $optDisplay = $role['display_price'] ?? ( $event->metode_pembayaran === 'online_banking' ? (int) round((float)$role['price'] * 1.03) : (float)$role['price']);
                                                @endphp
                                                <option value="{{ $role['name'] }}" {{ old('role_name') === $role['name'] ? 'selected' : '' }} @if(!empty($role['is_full'])) disabled @endif>
                                                    {{ $role['name'] }} — Rp {{ number_format($optDisplay, 0, ',', '.') }} ({{ $role['slots'] }} slot) @if(!empty($role['is_full'])) - Penuh @endif
                                                </option>
                                            @endforeach
                                </select>
                                @error('role_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>
                        @endif
                        <div>
                            <label for="nama_mobile" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap</label>
                            <input id="nama_mobile" name="nama" type="text" required value="{{ old('nama') }}" placeholder="Masukkan nama sesuai KTP"
                                   class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10">
                            @error('nama')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="kontak_mobile" class="block text-sm font-medium text-gray-700 mb-1.5">Nomor Telepon / WhatsApp</label>
                            <input id="kontak_mobile" name="kontak" type="text" required value="{{ old('kontak') }}" placeholder="Contoh: 08123456789"
                                   class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10">
                            @error('kontak')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="w-full py-3 rounded-xl bg-lime-400 hover:bg-lime-500 text-brand-900 font-bold text-sm transition-all shadow-lg shadow-lime-400/20 flex items-center justify-center gap-2">
                            Gabung Sekarang
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </form>
                </div>
                @endif
            </div>

            {{-- Right Column: Slot + Roster + Desktop Form --}}
            <div class="space-y-6">
                {{-- Slot Card --}}
                <div class="pro-card p-6">
                    <div class="flex items-baseline justify-between mb-3">
                        <h3 class="text-base font-semibold text-gray-900">Slot Tersedia</h3>
                        <p class="text-2xl font-bold text-gray-900"><span class="text-brand-500">{{ $slotsLeft }}</span>/{{ $event->slot_max }}</p>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden mb-4">
                        <div class="h-full rounded-full transition-all duration-500 {{ $isFull ? 'bg-rose-500' : 'bg-lime-400' }}" style="{{ 'width: '.$slotPercentage.'%;' }}"></div>
                    </div>

                    @if($isFull)
                        <div class="rounded-xl bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-700">
                            Slot sudah penuh. Pantau info admin untuk jadwal berikutnya.
                        </div>
                    @else
                        {{-- Desktop form --}}
                        <div class="hidden lg:block">
                            @if(session('error'))
                                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ session('error') }}</div>
                            @endif
                            <form action="{{ route('player.join.store', $event->slug) }}" method="POST" class="space-y-3">
                                @csrf
                                @if($isCustomEvent)
                                    <div>
                                        <label for="role_desktop" class="block text-sm font-medium text-gray-700 mb-1.5">Pilih Role</label>
                                        <select id="role_desktop" name="role_name" required
                                                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 bg-white">
                                            <option value="">Pilih role...</option>
                                            @foreach($customRoles as $role)
                                                @php
                                                    $optDisplay = $role['display_price'] ?? ( $event->metode_pembayaran === 'online_banking' ? (int) round((float)$role['price'] * 1.03) : (float)$role['price']);
                                                @endphp
                                                <option value="{{ $role['name'] }}" {{ old('role_name') === $role['name'] ? 'selected' : '' }} @if(!empty($role['is_full'])) disabled @endif>
                                                    {{ $role['name'] }} — Rp {{ number_format($optDisplay, 0, ',', '.') }} ({{ $role['slots'] }} slot) @if(!empty($role['is_full'])) - Penuh @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('role_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                    </div>
                                @endif
                                <input name="nama" type="text" required value="{{ old('nama') }}" placeholder="Nama lengkap"
                                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10">
                                @error('nama')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                <input name="kontak" type="text" required value="{{ old('kontak') }}" placeholder="No. WhatsApp"
                                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10">
                                @error('kontak')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                <p class="text-[10px] text-slate-500 mt-2">Pembayaran melalui Midtrans QRIS, jumlah yang tertera sudah termasuk admin.</p>
                                <button type="submit" class="w-full py-3 rounded-xl bg-lime-400 hover:bg-lime-500 text-brand-900 font-bold text-sm transition-all shadow-lg shadow-lime-400/20 flex items-center justify-center gap-2">
                                    Gabung Sekarang
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </button>
                            </form>
                        </div>
                        <p class="text-xs text-gray-400 text-center mt-3">Sisa {{ $slotsLeft }} slot. Pembayaran via {{ strtolower($paymentLabel) }}.</p>
                    @endif
                </div>

                {{-- Roster --}}
                @if($event->show_joined_players_public)
                <div class="pro-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-semibold text-gray-900">Roster ({{ $joinedCount }})</h3>
                    </div>

                    @if($isCustomEvent)
                        @if(collect($customRoles)->isEmpty())
                            <p class="text-xs text-gray-400 text-center py-4">Belum ada role yang ditentukan.</p>
                        @elseif($joinedPlayers->isEmpty())
                            <p class="text-xs text-gray-400 text-center py-4">Belum ada pemain. Jadilah yang pertama!</p>
                        @else
                            <div class="space-y-4 max-h-80 overflow-y-auto pr-1">
                                @foreach($customRoles as $role)
                                    @php
                                        $playersInRole = $joinedPlayers->get($role['name'], collect());
                                    @endphp
                                    <div class="rounded-3xl border border-gray-200 bg-slate-50 p-4">
                                        <div class="flex items-center justify-between gap-3 mb-3">
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">{{ $role['name'] }}</p>
                                                <p class="text-xs text-gray-500">{{ $playersInRole->count() }} / {{ $role['slots'] }} terisi</p>
                                            </div>
                                            <span class="text-[11px] font-semibold {{ !empty($role['is_full']) ? 'text-rose-600' : 'text-emerald-600' }}">
                                                {{ !empty($role['is_full']) ? 'Penuh' : 'Sisa '.$role['slots_left'].' slot' }}
                                            </span>
                                        </div>

                                        @if($playersInRole->isEmpty())
                                            <p class="text-xs text-gray-500">Belum ada pemain untuk role ini.</p>
                                        @else
                                            <div class="space-y-3">
                                                @foreach($playersInRole as $player)
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center gap-3">
                                                            <div class="w-9 h-9 rounded-full bg-brand-50 flex items-center justify-center text-brand-600 text-xs font-bold">
                                                                {{ strtoupper(substr($player->nama, 0, 2)) }}
                                                            </div>
                                                            <div>
                                                                <p class="text-sm font-semibold text-gray-800">{{ $player->nama }}</p>
                                                                @if($event->show_joined_player_contacts_public)
                                                                    <p class="text-xs text-gray-400">{{ $player->kontak }}</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        @if($player->pivot && $player->pivot->payment_status === 'paid')
                                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-lime-400 text-brand-900 uppercase tracking-wider">Lunas</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @else
                        @if($joinedPlayers->isEmpty())
                            <p class="text-xs text-gray-400 text-center py-4">Belum ada pemain. Jadilah yang pertama!</p>
                        @else
                            <div class="space-y-3 max-h-60 overflow-y-auto pr-1">
                                @foreach($joinedPlayers as $player)
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-brand-50 flex items-center justify-center text-brand-600 text-xs font-bold">
                                                {{ strtoupper(substr($player->nama, 0, 2)) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-800">{{ $player->nama }}</p>
                                                @if($event->show_joined_player_contacts_public)
                                                    <p class="text-xs text-gray-400">{{ $player->kontak }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        @if($player->pivot && $player->pivot->payment_status === 'paid')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-lime-400 text-brand-900 uppercase tracking-wider">Lunas</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>

