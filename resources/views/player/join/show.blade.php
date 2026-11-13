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
                        brand: { 50: '#E6EEFF', 100: '#CCDDFF', 500: '#0052FF', 600: '#0042CC', 700: '#003199', 900: '#0A1628' },
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
        .section-bar { width: 4px; height: 18px; border-radius: 9999px; background: #0052FF; }
    </style>
</head>
<body class="bg-surface min-h-screen">

    @php
        $slotPercentage = $event->slot_max > 0 ? min(100, (int) round(($occupiedCount / $event->slot_max) * 100)) : 0;
        $slotsLeft = max(0, $event->slot_max - $occupiedCount);
        $isCustomEvent = $event->skema_iuran === 'custom';
        $rolesWithAvailability = $rolesWithAvailability ?? [];
        $customRoles = count($rolesWithAvailability) ? $rolesWithAvailability : ($event->roles ?? []);
        $computeAdminFee = fn (float $base, string $method) => \App\Support\PaymentFee::adminFee($base, $method);
        $rupiah = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
        $initials = fn ($name) => Str::upper(Str::substr(collect(preg_split('/\s+/', trim((string) $name)))->filter()->take(2)->map(fn ($w) => Str::substr($w, 0, 1))->implode(''), 0, 2)) ?: '?';

        // Harga untuk event non-custom (flat / loyalitas)
        $basePrice = (float) $event->iuran_per_pemain;
        $displayPrice = (int) round($basePrice + $computeAdminFee($basePrice, $event->metode_pembayaran));
        if ($event->skema_iuran === 'loyalitas') {
            $baseEst = $event->slot_max > 0 ? ($event->biaya_total_event / $event->slot_max) : 0;
            $minEst = round($baseEst * 0.70 + $computeAdminFee($baseEst * 0.70, $event->metode_pembayaran), -2);
            $maxEst = round($baseEst + $computeAdminFee($baseEst, $event->metode_pembayaran), -2);
        }

        $canWaitlist = $isFull && $event->enable_waiting_list;
        $showForm = empty($pendingJoinPlayer) && (!$isFull || $event->enable_waiting_list);
        $payFirst = $event->requiresPaymentFirst();
        $organizer = $event->admin?->name ?? 'Penyelenggara';
        $waktu = Str::substr((string) $event->waktu, 0, 5);
        $mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($event->tempat);

        // Data posisi untuk kartu pilihan & ringkasan biaya
        $roleData = collect($customRoles)->map(function ($role) use ($computeAdminFee, $event, $canWaitlist) {
            $price = (float) ($role['price'] ?? 0);
            $admin = (float) ($role['admin_fee'] ?? $computeAdminFee($price, $event->metode_pembayaran));
            $isRoleFull = !empty($role['is_full']);

            return [
                'name' => $role['name'],
                'price' => $price,
                'admin_fee' => $admin,
                'total' => (float) ($role['display_price'] ?? ($price + $admin)),
                'slots' => (int) ($role['slots'] ?? 0),
                'slots_left' => (int) ($role['slots_left'] ?? ($role['slots'] ?? 0)),
                'is_full' => $isRoleFull,
                'selectable' => !$isRoleFull || $canWaitlist,
            ];
        })->values();

        $selectedRole = old('role_name');
        if (!$selectedRole || !$roleData->firstWhere('name', $selectedRole)) {
            $selectedRole = optional($roleData->firstWhere('selectable', true))['name'] ?? null;
        }

        $statusBadge = $isFull
            ? ($event->enable_waiting_list ? ['Waiting List Dibuka', 'bg-amber-400 text-brand-900'] : ['Slot Penuh', 'bg-rose-500 text-white'])
            : ['Pendaftaran Dibuka', 'bg-lime-400 text-brand-900'];
    @endphp

    {{-- Navbar --}}
    <nav class="sticky top-0 z-40 bg-brand-900 border-b border-slate-800 text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14">
                <img src="{{ asset('images/Logo/Horizontal/Secondary.png') }}" class="h-8 object-contain" alt="{{ config('app.name', 'Atturin') }}">
                <button type="button" id="btn-share" class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-white/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                    <span id="btn-share-label">Bagikan</span>
                </button>
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-5 lg:py-8 fade-up">
        {{-- Breadcrumb --}}
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
            <p class="min-w-0 truncate"><span>Event Komunitas</span> <span class="mx-1 text-gray-300">/</span> <span class="font-semibold text-gray-800">{{ $event->nama_event }}</span></p>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-2.5 py-1 font-bold text-brand-600">Kode: {{ $event->join_code }}</span>
        </div>

        {{-- Flash --}}
        @foreach(['success' => 'emerald', 'info' => 'blue'] as $flashKey => $tone)
            @if(session($flashKey))
                <div class="mb-4 rounded-xl border border-{{ $tone }}-200 bg-{{ $tone }}-50 px-4 py-3 text-sm text-{{ $tone }}-700">{{ session($flashKey) }}</div>
            @endif
        @endforeach

        {{-- Hero --}}
        <section class="relative overflow-hidden rounded-3xl bg-brand-900 text-white shadow-xl">
            @if($event->banner_image)
                <img src="{{ asset('storage/' . $event->banner_image) }}" alt="" class="absolute inset-0 h-full w-full object-cover">
            @endif
            <div class="absolute inset-0 bg-gradient-to-r from-brand-900 via-brand-900/90 to-brand-700/70"></div>
            <div class="relative p-5 sm:p-8">
                <div class="flex flex-wrap gap-2">
                    <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-wider {{ $statusBadge[1] }}">{{ $statusBadge[0] }}</span>
                    <span class="rounded-full bg-white/10 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-white/90">
                        {{ $payFirst ? 'Bayar dulu · QRIS' : 'Daftar dulu · bayar menyusul' }}
                    </span>
                </div>
                <h1 class="mt-4 text-2xl sm:text-4xl font-bold leading-tight break-words">{{ $event->nama_event }}</h1>
                <p class="mt-2 text-sm text-white/70">{{ \Carbon\Carbon::parse($event->tanggal)->translatedFormat('l, d F Y') }} · {{ $waktu }} WIB · {{ $event->tempat }}</p>

                <div class="mt-6 grid grid-cols-1 gap-3 rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur sm:grid-cols-2">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-full bg-brand-500 text-sm font-bold">{{ $initials($organizer) }}</span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold">{{ $organizer }}</p>
                            <p class="text-xs text-white/60">Penyelenggara</p>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-baseline justify-between gap-2 text-xs">
                            <span class="font-semibold text-white/70">Kapasitas</span>
                            <span class="font-bold text-lime-400">{{ $occupiedCount }} dari {{ $event->slot_max }} slot terisi</span>
                        </div>
                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-white/15">
                            <div class="h-full rounded-full {{ $isFull ? 'bg-rose-400' : 'bg-lime-400' }}" style="width: {{ $slotPercentage }}%"></div>
                        </div>
                        <p class="mt-1.5 text-right text-[11px] text-white/60">
                            @if($isFull)
                                Slot penuh{{ $event->enable_waiting_list ? ' · ' . ($waitingCount ?? 0) . ' orang di waiting list' : '' }}
                            @else
                                Sisa {{ $slotsLeft }} slot
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Info cards --}}
        <section class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
            @php
                $infoCards = [
                    ['Tanggal', \Carbon\Carbon::parse($event->tanggal)->translatedFormat('l, d M Y'), null, '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>'],
                    ['Kick-off', $waktu . ' WIB', 'Datang 15 menit lebih awal', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
                    ['Venue', $event->tempat, 'Buka di Google Maps', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>'],
                    ['Slot', $event->slot_max . ' pemain', $isCustomEvent ? count($customRoles) . ' posisi' : null, '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>'],
                ];
            @endphp
            @foreach($infoCards as [$label, $value, $sub, $icon])
                <div class="pro-card flex items-start gap-3 p-3.5 sm:p-4 min-w-0">
                    <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $icon !!}</svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ $label }}</p>
                        <p class="text-sm font-bold text-gray-900 break-words">{{ $value }}</p>
                        @if($sub)
                            @if($label === 'Venue')
                                <a href="{{ $mapsUrl }}" target="_blank" rel="noopener" class="text-[11px] font-semibold text-brand-500 hover:underline">{{ $sub }} ↗</a>
                            @else
                                <p class="text-[11px] text-gray-400">{{ $sub }}</p>
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach
        </section>

        <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-3 lg:items-start">

            {{-- ═══ Kolom kiri (atas): fasilitas & posisi ═══ --}}
            <div class="space-y-5 lg:col-span-2 min-w-0">
                @if(!empty($event->facilities) && is_array($event->facilities))
                    <section class="pro-card p-5 sm:p-6">
                        <div class="mb-4 flex items-center gap-2">
                            <span class="section-bar"></span>
                            <h2 class="text-base font-bold text-gray-900">Fasilitas Tersedia</h2>
                        </div>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            @foreach($event->facilities as $facility)
                                @php
                                    $facilityKey = is_array($facility) ? ($facility['key'] ?? null) : null;
                                    $facilityLabel = $facilityKey ? \App\Support\FacilityCatalog::label($facilityKey) : (is_string($facility) ? $facility : '');
                                    $facilityNote = is_array($facility) ? ($facility['note'] ?? null) : null;
                                @endphp
                                <div class="rounded-xl bg-gray-50 p-3.5">
                                    <svg class="mb-2 h-5 w-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! \App\Support\FacilityCatalog::icon((string) $facilityKey) !!}</svg>
                                    <p class="text-sm font-bold text-gray-900">{{ $facilityLabel }}</p>
                                    @if($facilityNote)
                                        <p class="mt-0.5 text-[11px] text-gray-500 break-words">{{ $facilityNote }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if($isCustomEvent && $roleData->isNotEmpty())
                    <section class="pro-card p-5 sm:p-6" id="pilih-posisi">
                        <div class="mb-1 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="section-bar bg-lime-400" style="background:#ABD600"></span>
                                <h2 class="text-base font-bold text-gray-900">Pilih Posisi</h2>
                            </div>
                            <span class="rounded-full bg-brand-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-brand-600">Kuota real-time</span>
                        </div>
                        <p class="mb-4 text-xs text-gray-500">Harga sudah termasuk biaya admin pembayaran.</p>

                        <div class="space-y-3">
                            @foreach($roleData as $role)
                                <button type="button"
                                        class="role-card group flex w-full items-center gap-3 rounded-2xl border-2 p-4 text-left transition-all disabled:cursor-not-allowed"
                                        data-role="{{ $role['name'] }}"
                                        @disabled(!$role['selectable'] || !$showForm)>
                                    <span class="role-initial flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl text-base font-bold">{{ Str::upper(Str::substr($role['name'], 0, 1)) }}</span>
                                    <span class="min-w-0 flex-1">
                                        <span class="flex flex-wrap items-center gap-2">
                                            <span class="text-sm sm:text-base font-bold text-gray-900">{{ $role['name'] }}</span>
                                            @if($role['is_full'])
                                                <span class="rounded-md bg-gray-200 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider text-gray-500">{{ $canWaitlist ? 'Waiting list' : 'Penuh' }}</span>
                                            @elseif($role['slots_left'] <= 2)
                                                <span class="rounded-md bg-amber-100 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider text-amber-700">Hampir penuh</span>
                                            @endif
                                        </span>
                                        <span class="mt-1 flex items-center gap-1.5 text-xs {{ $role['is_full'] ? 'text-rose-500' : 'text-emerald-600' }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $role['is_full'] ? 'bg-rose-500' : 'bg-emerald-500' }}"></span>
                                            {{ $role['slots_left'] }} dari {{ $role['slots'] }} slot tersisa
                                        </span>
                                    </span>
                                    <span class="flex-shrink-0 text-right">
                                        <span class="block text-[10px] font-bold uppercase tracking-wider text-gray-400">Biaya</span>
                                        <span class="block text-sm sm:text-lg font-bold {{ $role['is_full'] && !$canWaitlist ? 'text-gray-400' : 'text-gray-900' }}">{{ $role['total'] > 0 ? $rupiah($role['total']) : 'Gratis' }}</span>
                                        <span class="role-selected-badge mt-1 hidden items-center gap-1 rounded-full bg-brand-500 px-2 py-0.5 text-[10px] font-bold text-white">✓ Dipilih</span>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            {{-- ═══ Kolom kanan: kartu daftar (sticky) ═══ --}}
            <aside class="lg:col-start-3 lg:row-start-1 lg:row-span-2 lg:sticky lg:top-20 space-y-4 min-w-0" id="form-daftar">
                @if(!empty($pendingJoinPlayer))
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800">
                        <p class="font-bold text-amber-900">Anda punya pendaftaran yang belum selesai</p>
                        <p class="mt-1 text-xs">Selesaikan pembayaran atau batalkan sebelum mendaftar lagi.</p>
                        <dl class="mt-3 space-y-1 text-xs">
                            <div class="flex justify-between gap-2"><dt>Nama</dt><dd class="font-semibold text-right">{{ $pendingJoinPlayer->nama }}</dd></div>
                            <div class="flex justify-between gap-2"><dt>Posisi</dt><dd class="font-semibold text-right">{{ $pendingJoinPlayer->pivot->role_name ?? '-' }}</dd></div>
                            <div class="flex justify-between gap-2"><dt>Status</dt><dd class="font-semibold text-right">{{ $pendingJoinPlayer->pivot->status_join === 'hold' ? 'Menunggu pembayaran' : 'Belum lunas' }}</dd></div>
                        </dl>
                        <div class="mt-4 grid gap-2">
                            <a href="{{ $pendingJoinPlayer->pivot->registration_token ? route('registration.show', $pendingJoinPlayer->pivot->registration_token) : route('player.join.success', $event->slug) }}"
                               class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-brand-600">Lanjutkan Pembayaran</a>
                            <form action="{{ $pendingJoinPlayer->pivot->registration_token ? route('registration.cancel', $pendingJoinPlayer->pivot->registration_token) : route('player.join.cancel', $event->slug) }}" method="POST"
                                  onsubmit="return confirm('Batalkan pendaftaran ini?')">
                                @csrf
                                <button type="submit" class="w-full rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-600 hover:bg-rose-50">Batalkan Pendaftaran</button>
                            </form>
                        </div>
                    </div>
                @elseif(!$showForm)
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5 text-sm text-rose-700">
                        <p class="font-bold text-rose-800">Slot sudah penuh</p>
                        <p class="mt-1 text-xs">Pantau info penyelenggara untuk jadwal berikutnya.</p>
                    </div>
                @else
                    <div class="overflow-hidden rounded-2xl bg-white shadow-xl ring-1 ring-gray-200">
                        <div class="bg-brand-900 px-5 py-4 text-white">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-white/60">{{ $canWaitlist ? 'Masuk waiting list' : 'Slot dipilih' }}</p>
                                <span class="flex items-center gap-1 text-[10px] font-bold text-lime-400"><span class="h-1.5 w-1.5 rounded-full bg-lime-400"></span>Live</span>
                            </div>
                            <div class="mt-1 flex items-end justify-between gap-3">
                                <p id="sum-role" class="min-w-0 truncate text-lg font-bold">{{ $isCustomEvent ? ($selectedRole ?? 'Pilih posisi') : 'Peserta' }}</p>
                                <p id="sum-total-head" class="flex-shrink-0 text-lg font-bold text-lime-400">
                                    @if(!$isCustomEvent)
                                        {{ $event->skema_iuran === 'loyalitas' ? $rupiah($minEst) . '+' : ($displayPrice > 0 ? $rupiah($displayPrice) : 'Gratis') }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="p-5">
                            @if(session('error'))
                                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5 text-sm text-rose-700">{{ session('error') }}</div>
                            @endif

                            <div class="mb-4 space-y-1.5 rounded-xl bg-gray-50 p-3 text-xs text-gray-600">
                                <p class="flex items-center gap-2"><svg class="h-3.5 w-3.5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>{{ \Carbon\Carbon::parse($event->tanggal)->translatedFormat('D, d M Y') }} · {{ $waktu }} WIB</p>
                                <p class="flex items-center gap-2 min-w-0"><svg class="h-3.5 w-3.5 flex-shrink-0 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg><span class="truncate">{{ $event->tempat }}</span></p>
                            </div>

                            <p id="sum-warning" class="mb-4 hidden rounded-xl bg-lime-400/20 px-3 py-2 text-xs font-semibold text-brand-900"></p>

                            <form action="{{ route('player.join.store', $event->slug) }}" method="POST" class="space-y-3" id="join-form">
                                @csrf
                                @if($isCustomEvent)
                                    <input type="hidden" name="role_name" id="role_name" value="{{ $selectedRole }}">
                                    @error('role_name')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                                @endif
                                <div>
                                    <label for="nama" class="mb-1 block text-xs font-semibold text-gray-700">Nama Lengkap <span class="text-rose-500">*</span></label>
                                    <input id="nama" name="nama" type="text" required maxlength="255" value="{{ old('nama') }}" placeholder="Nama yang tampil di roster"
                                           class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10">
                                    @error('nama')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="kontak" class="mb-1 block text-xs font-semibold text-gray-700">Nomor WhatsApp <span class="text-rose-500">*</span></label>
                                    <input id="kontak" name="kontak" type="tel" inputmode="tel" required maxlength="255" value="{{ old('kontak') }}" placeholder="08xxxxxxxxxx"
                                           class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10">
                                    @error('kontak')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>

                                {{-- Rincian biaya --}}
                                <dl class="space-y-1.5 border-t border-dashed border-gray-200 pt-3 text-xs">
                                    @if($isCustomEvent)
                                        <div class="flex justify-between gap-2 text-gray-500"><dt>Biaya posisi</dt><dd id="sum-price">-</dd></div>
                                        <div class="flex justify-between gap-2 text-gray-500"><dt>Biaya layanan QRIS</dt><dd id="sum-admin">-</dd></div>
                                        <div class="flex justify-between gap-2 pt-1 text-sm font-bold text-gray-900"><dt>Total</dt><dd id="sum-total" class="text-brand-600">-</dd></div>
                                    @elseif($event->skema_iuran === 'loyalitas')
                                        <div class="flex justify-between gap-2 text-sm font-bold text-gray-900"><dt>Estimasi biaya</dt><dd class="text-brand-600">{{ $rupiah($minEst) }} – {{ $rupiah($maxEst) }}</dd></div>
                                    @else
                                        <div class="flex justify-between gap-2 text-sm font-bold text-gray-900"><dt>Total</dt><dd class="text-brand-600">{{ $displayPrice > 0 ? $rupiah($displayPrice) : 'Gratis' }}</dd></div>
                                    @endif
                                </dl>

                                @if($canWaitlist)
                                    <p class="text-[11px] text-gray-500">Slot sedang penuh. Anda akan masuk waiting list dan dihubungi bila ada slot kosong.</p>
                                @else
                                    @include('player.join.partials.payment-mode-note', ['compact' => true])
                                @endif

                                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand-500 py-3 text-sm font-bold text-white shadow-lg shadow-brand-500/25 hover:bg-brand-600">
                                    {{ $canWaitlist ? 'Masuk Waiting List' : ($payFirst ? 'Daftar & Bayar Sekarang' : 'Daftar Sekarang') }}
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </button>
                            </form>

                            <div class="mt-4 flex items-center justify-center gap-4 text-[10px] font-semibold text-gray-400">
                                <span class="flex items-center gap-1"><svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>QRIS Midtrans</span>
                                <span class="flex items-center gap-1"><svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>Info via WhatsApp</span>
                            </div>
                        </div>
                    </div>
                @endif

                @if($event->team_draw_published_at)
                    <a href="{{ route('player.teams.show', $event->slug) }}" class="pro-card flex items-center justify-between gap-3 p-4 hover:border-brand-500">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-brand-500">Pembagian Tim</p>
                            <p class="mt-0.5 text-sm text-gray-600">Lihat tim dan jadwal pertandingan</p>
                        </div>
                        <svg class="h-5 w-5 flex-shrink-0 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                @endif
            </aside>

            {{-- ═══ Kolom kiri (bawah): roster ═══ --}}
            @if($event->show_joined_players_public)
                <section class="pro-card p-5 sm:p-6 lg:col-span-2 min-w-0">
                    <div class="mb-1 flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="section-bar"></span>
                            <h2 class="text-base font-bold text-gray-900">Siapa yang Ikut?</h2>
                            <span class="rounded-full bg-brand-50 px-2 py-0.5 text-[11px] font-bold text-brand-600">{{ $joinedCount }}/{{ $event->slot_max }}</span>
                        </div>
                    </div>
                    <p class="mb-5 text-xs text-gray-500">Peserta yang sudah terdaftar.{{ $event->show_joined_player_contacts_public ? '' : ' Kontak peserta disembunyikan.' }}</p>

                    @php
                        $playerCard = function ($player) use ($event, $initials) {
                            return ['nama' => $player->nama, 'kontak' => $event->show_joined_player_contacts_public ? $player->kontak : null,
                                    'paid' => $player->pivot && $player->pivot->payment_status === 'paid', 'initials' => $initials($player->nama)];
                        };
                    @endphp

                    @if($isCustomEvent)
                        @if($roleData->isEmpty())
                            <p class="py-4 text-center text-xs text-gray-400">Belum ada posisi yang ditentukan.</p>
                        @else
                            <div class="space-y-6">
                                @foreach($roleData as $role)
                                    @php
                                        $players = $joinedPlayers->get($role['name'], collect());
                                        $processing = max(0, $role['slots'] - $players->count() - $role['slots_left']);
                                        $emptyShown = min($role['slots_left'], 4);
                                    @endphp
                                    <div>
                                        <div class="mb-2.5 flex items-center justify-between gap-2">
                                            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-500">{{ $role['name'] }} <span class="text-gray-400">({{ $players->count() }} dari {{ $role['slots'] }} terdaftar)</span></p>
                                            <span class="text-[11px] font-bold {{ $role['slots_left'] > 0 ? 'text-brand-500' : 'text-gray-400' }}">{{ $role['slots_left'] > 0 ? $role['slots_left'] . ' slot kosong' : 'Penuh' }}</span>
                                        </div>
                                        <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                                            @foreach($players as $player)
                                                @php $p = $playerCard($player); @endphp
                                                <div class="flex items-center gap-3 rounded-xl bg-gray-50 p-3 min-w-0">
                                                    <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-brand-900 text-xs font-bold text-white">{{ $p['initials'] }}</span>
                                                    <div class="min-w-0 flex-1">
                                                        <p class="truncate text-sm font-semibold text-gray-900">{{ $p['nama'] }}</p>
                                                        @if($p['kontak'])<p class="truncate text-[11px] text-gray-400">{{ $p['kontak'] }}</p>@endif
                                                    </div>
                                                    <span class="flex-shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold {{ $p['paid'] ? 'bg-lime-400 text-brand-900' : 'bg-gray-200 text-gray-500' }}">{{ $p['paid'] ? '✓ Lunas' : 'Belum bayar' }}</span>
                                                </div>
                                            @endforeach
                                            @for($i = 0; $i < $processing; $i++)
                                                <div class="flex items-center gap-3 rounded-xl border border-dashed border-amber-300 bg-amber-50/60 p-3">
                                                    <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600">⏳</span>
                                                    <p class="text-xs font-semibold text-amber-700">Sedang dalam proses pembayaran</p>
                                                </div>
                                            @endfor
                                            @for($i = 0; $i < $emptyShown; $i++)
                                                <div class="flex items-center gap-3 rounded-xl border-2 border-dashed border-brand-100 p-3">
                                                    <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-brand-50 text-lg font-bold text-brand-500">+</span>
                                                    <div class="min-w-0 flex-1">
                                                        <p class="text-sm font-semibold text-brand-600">Slot tersedia</p>
                                                        <p class="text-[11px] text-gray-400">Bisa jadi milik Anda</p>
                                                    </div>
                                                    @if($showForm && empty($pendingJoinPlayer) && $role['selectable'])
                                                        <button type="button" class="claim-btn flex-shrink-0 rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-brand-600" data-role="{{ $role['name'] }}">Klaim</button>
                                                    @endif
                                                </div>
                                            @endfor
                                            @if($role['slots_left'] > $emptyShown)
                                                <p class="self-center text-xs text-gray-400 sm:col-span-2">+{{ $role['slots_left'] - $emptyShown }} slot kosong lainnya</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @else
                        @if($joinedPlayers->isEmpty())
                            <p class="py-4 text-center text-xs text-gray-400">Belum ada pemain. Jadilah yang pertama!</p>
                        @else
                            <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                                @foreach($joinedPlayers as $player)
                                    @php $p = $playerCard($player); @endphp
                                    <div class="flex items-center gap-3 rounded-xl bg-gray-50 p-3 min-w-0">
                                        <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-brand-900 text-xs font-bold text-white">{{ $p['initials'] }}</span>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-semibold text-gray-900">{{ $p['nama'] }}</p>
                                            @if($p['kontak'])<p class="truncate text-[11px] text-gray-400">{{ $p['kontak'] }}</p>@endif
                                        </div>
                                        <span class="flex-shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold {{ $p['paid'] ? 'bg-lime-400 text-brand-900' : 'bg-gray-200 text-gray-500' }}">{{ $p['paid'] ? '✓ Lunas' : 'Belum bayar' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </section>
            @endif
        </div>
    </main>

    <footer class="mt-8 border-t border-gray-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 px-4 py-5 text-xs text-gray-400 sm:flex-row sm:px-6 lg:px-8">
            <p><span class="font-bold text-brand-900">ATTURIN</span> · Manajemen event olahraga komunitas</p>
            <p>© {{ now()->year }} {{ config('app.name', 'Atturin') }}</p>
        </div>
    </footer>

    <script>
    (function () {
        const roles = @json($roleData);
        const rupiah = (v) => 'Rp ' + Math.round(v || 0).toLocaleString('id-ID');
        const roleInput = document.getElementById('role_name');
        const cards = [...document.querySelectorAll('.role-card')];

        function selectRole(name) {
            const role = roles.find((r) => r.name === name);
            if (!role || !role.selectable) return;
            if (roleInput) roleInput.value = name;

            cards.forEach((card) => {
                const active = card.dataset.role === name;
                const disabled = card.disabled;
                card.classList.toggle('border-brand-500', active);
                card.classList.toggle('bg-brand-50', active);
                card.classList.toggle('shadow-lg', active);
                card.classList.toggle('border-gray-100', !active);
                card.classList.toggle('bg-gray-50', !active && !disabled);
                card.classList.toggle('opacity-50', disabled);
                card.querySelector('.role-initial').className = 'role-initial flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl text-base font-bold ' +
                    (active ? 'bg-brand-900 text-lime-400' : 'bg-white text-gray-500 border border-gray-200');
                const badge = card.querySelector('.role-selected-badge');
                badge.classList.toggle('hidden', !active);
                badge.classList.toggle('inline-flex', active);
            });

            const set = (id, text) => { const el = document.getElementById(id); if (el) el.textContent = text; };
            set('sum-role', role.name);
            set('sum-total-head', role.total > 0 ? rupiah(role.total) : 'Gratis');
            set('sum-price', role.price > 0 ? rupiah(role.price) : 'Gratis');
            set('sum-admin', rupiah(role.admin_fee));
            set('sum-total', role.total > 0 ? rupiah(role.total) : 'Gratis');

            const warning = document.getElementById('sum-warning');
            if (warning) {
                const show = !role.is_full && role.slots_left <= 2;
                warning.classList.toggle('hidden', !show);
                warning.textContent = show ? `⚡ Tinggal ${role.slots_left} slot untuk posisi ${role.name}` : '';
            }
        }

        cards.forEach((card) => {
            if (card.disabled) card.classList.add('border-gray-100', 'opacity-50');
            card.addEventListener('click', () => selectRole(card.dataset.role));
        });
        if (roleInput && roleInput.value) {
            selectRole(roleInput.value);
        } else {
            cards.forEach((card) => card.classList.add('border-gray-100', 'bg-gray-50'));
        }

        // Klaim slot kosong dari roster → pilih posisi & fokus ke form
        document.querySelectorAll('.claim-btn').forEach((btn) => btn.addEventListener('click', () => {
            selectRole(btn.dataset.role);
            document.getElementById('form-daftar').scrollIntoView({ behavior: 'smooth', block: 'start' });
            setTimeout(() => document.getElementById('nama')?.focus({ preventScroll: true }), 400);
        }));

        // Bagikan link event
        const shareBtn = document.getElementById('btn-share');
        shareBtn?.addEventListener('click', async () => {
            const data = { title: @json($event->nama_event), url: window.location.href };
            try {
                if (navigator.share) { await navigator.share(data); return; }
                await navigator.clipboard.writeText(data.url);
                const label = document.getElementById('btn-share-label');
                label.textContent = 'Link disalin';
                setTimeout(() => { label.textContent = 'Bagikan'; }, 2000);
            } catch (e) { /* dibatalkan pengguna */ }
        });
    })();
    </script>
</body>
</html>
