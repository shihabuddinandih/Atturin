@extends('layouts.admin')

@php
    $draftState = $draftState ?? null;
    $facilityOptions = \App\Support\FacilityCatalog::options();

    // Langkah yang dibuka pertama: error validasi server > posisi terakhir draf > langkah 1
    $errorStep = null;
    if ($errors->any()) {
        $keys = collect($errors->keys());
        if ($keys->contains(fn ($k) => in_array($k, ['nama_event', 'tanggal', 'waktu', 'tempat', 'banner_image']) || str_starts_with($k, 'facilities'))) {
            $errorStep = 1;
        } elseif ($keys->contains(fn ($k) => str_starts_with($k, 'roles'))) {
            $errorStep = 2;
        } else {
            $errorStep = 3;
        }
    }

    $paymentMode = old('payment_mode', $draftState['payment_mode'] ?? 'pay_later');
    $waitingListOn = old('enable_waiting_list', ($draftState['enable_waiting_list'] ?? false) ? '1' : '0') == '1';
    $paymentModes = [
        'pay_later' => [
            'title' => 'Daftar dulu, bayar menyusul',
            'desc' => 'Peserta langsung masuk roster. Link pelunasan dikirim via WhatsApp; nomor tidak valid akan digugurkan.',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>',
        ],
        'pay_first' => [
            'title' => 'Bayar dulu baru terdaftar',
            'desc' => 'Slot ditahan ' . \App\Models\Event::HOLD_MINUTES . ' menit selama peserta membayar QRIS. Tidak dibayar → slot dilepas.',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>',
        ],
    ];
    $draftShowPlayers = ($draftState['show_joined_players_public'] ?? true) ? '1' : '0';
    $draftShowContacts = ($draftState['show_joined_player_contacts_public'] ?? false) ? '1' : '0';

    $steps = [
        1 => ['title' => 'Detail Event', 'subtitle' => 'Info dasar & fasilitas', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>'],
        2 => ['title' => 'Posisi & Harga', 'subtitle' => 'Kuota & biaya per posisi', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>'],
        3 => ['title' => 'Review & Publikasi', 'subtitle' => 'Cek akhir sebelum rilis', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
    ];
@endphp

@push('styles')
<style>
    .wizard input[type=number]::-webkit-outer-spin-button,
    .wizard input[type=number]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .wizard input[type=number] { -moz-appearance: textfield; }
    .wizard .field-input {
        width: 100%; border-radius: 0.75rem; border: 1px solid #E5E7EB; background: #fff;
        padding: 0.625rem 1rem; font-size: 0.875rem; color: #1F2937; transition: all .15s ease;
    }
    .wizard .field-input:focus { border-color: #0052FF; box-shadow: 0 0 0 3px rgba(0,82,255,.1); outline: none; }
    .wizard .field-input.has-icon { padding-left: 2.5rem; }
    .wizard .field-label { display: block; font-size: 0.75rem; font-weight: 600; color: #374151; margin-bottom: 0.375rem; }
    .wizard .step-panel[hidden] { display: none; }
</style>
@endpush

@section('content')
<div class="wizard w-full max-w-6xl mx-auto pb-4">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-5">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-2">
                <span class="inline-flex items-center rounded-md bg-brand-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-brand-600">Pengatur Event</span>
                <span id="autosave-status" class="text-xs text-gray-400">{{ $draftState ? 'Draf dipulihkan' : 'Belum disimpan' }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Buat Event Baru</h1>
            <p class="text-sm text-gray-500 mt-1">Atur jadwal, posisi dengan kuota & harga masing-masing, lalu publikasikan link pendaftaran.</p>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <button type="button" id="btn-discard" class="inline-flex flex-1 sm:flex-none justify-center items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Buang
            </button>
            <button type="button" data-action="save-draft" class="inline-flex flex-1 sm:flex-none justify-center items-center gap-1.5 rounded-xl border border-gray-200 bg-gray-100 px-3.5 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                Simpan Draf
            </button>
        </div>
    </div>

    {{-- Stepper --}}
    <div class="grid grid-cols-3 gap-2 sm:gap-3 rounded-2xl border border-gray-200 bg-white p-2 mb-5">
        @foreach($steps as $num => $step)
            <button type="button" class="step-tab flex items-center gap-2 sm:gap-3 rounded-xl px-2 py-2.5 sm:px-4 sm:py-3 text-left transition-all" data-step="{{ $num }}">
                <span class="step-icon hidden sm:flex w-9 h-9 flex-shrink-0 rounded-lg items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $step['icon'] !!}</svg>
                </span>
                <span class="min-w-0">
                    <span class="step-kicker block text-[9px] sm:text-[10px] font-bold uppercase tracking-wider">Langkah 0{{ $num }}</span>
                    <span class="block text-xs sm:text-sm font-bold leading-tight truncate">{{ $step['title'] }}</span>
                    <span class="step-sub hidden md:block text-[11px] truncate">{{ $step['subtitle'] }}</span>
                </span>
            </button>
        @endforeach
    </div>

    <form id="event-form" action="{{ route('admin.events.store') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        <input type="hidden" name="draft_id" id="draft_id" value="{{ old('draft_id', $draftState['id'] ?? '') }}">
        <input type="hidden" name="skema_iuran" value="custom">
        <input type="hidden" name="metode_pembayaran" value="online_banking">

        {{-- ═════════════ LANGKAH 1 ═════════════ --}}
        <section class="step-panel space-y-5" data-panel="1">
            <div class="pro-card overflow-hidden">
                <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-4 py-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Informasi Event</h3>
                            <p class="text-xs text-gray-400">Jadwal dan lokasi pertandingan</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-6 space-y-5">
                    <div>
                        <div class="flex items-center justify-between">
                            <label for="nama_event" class="field-label">Nama Event <span class="text-rose-500">*</span></label>
                            <span class="text-[11px] text-gray-400 mb-1.5"><span id="nama-counter">0</span>/80</span>
                        </div>
                        <input type="text" name="nama_event" id="nama_event" maxlength="80" required
                               value="{{ old('nama_event', $draftState['nama_event'] ?? '') }}"
                               class="field-input" placeholder="Cth: Friday Night Fun Futsal">
                        <p class="mt-1.5 text-[11px] text-gray-400">Nama ini tampil di halaman pendaftaran dan bukti pembayaran peserta.</p>
                        @error('nama_event') <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="tanggal" class="field-label">Tanggal Event <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <svg class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <input type="date" name="tanggal" id="tanggal" required value="{{ old('tanggal', $draftState['tanggal'] ?? '') }}" class="field-input has-icon">
                            </div>
                            @error('tanggal') <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="waktu" class="field-label">Waktu Kick-off <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <svg class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <input type="time" name="waktu" id="waktu" required value="{{ old('waktu', $draftState['waktu'] ?? '') }}" class="field-input has-icon">
                            </div>
                            @error('waktu') <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="tempat" class="field-label">Tempat / Venue <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <input type="text" name="tempat" id="tempat" required maxlength="255" value="{{ old('tempat', $draftState['tempat'] ?? '') }}" class="field-input has-icon" placeholder="Cth: GOR Arena Futsal">
                        </div>
                        @error('tempat') <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Banner --}}
                    <div>
                        <label class="field-label">Hero Banner <span class="font-normal text-gray-400">(opsional)</span></label>
                        <div id="banner-drop-zone" class="border-2 border-dashed border-gray-200 hover:border-brand-500 rounded-2xl transition-all bg-gray-50/50 hover:bg-brand-50/20 cursor-pointer flex flex-col items-center justify-center min-h-[150px] text-center relative overflow-hidden group">
                            <input type="file" name="banner_image" id="banner_image" accept="image/jpeg,image/png,image/webp" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                            <div id="banner-placeholder" class="flex flex-col items-center justify-center gap-2 p-6">
                                <span class="w-11 h-11 rounded-full bg-white border border-gray-100 flex items-center justify-center text-gray-400 group-hover:text-brand-500 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </span>
                                <p class="text-sm font-semibold text-gray-800">Pilih atau seret gambar banner</p>
                                <p class="text-xs text-gray-400">JPG, PNG, atau WebP · maks. 2MB</p>
                            </div>
                            <div id="banner-preview-container" class="hidden absolute inset-0 bg-gray-900">
                                <img id="banner-preview-img" src="" alt="Preview banner" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-black/10 flex flex-col justify-end p-4 text-left">
                                    <p class="text-[11px] font-semibold text-white/80 uppercase tracking-wider">Preview Banner</p>
                                    <p id="banner-filename" class="text-sm font-bold text-white truncate max-w-[80%]"></p>
                                </div>
                            </div>
                        </div>
                        <div id="banner-actions" class="hidden mt-2">
                            <button type="button" id="btn-remove-banner" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Hapus Gambar
                            </button>
                        </div>
                        @error('banner_image') <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Fasilitas --}}
            <div class="pro-card p-4 sm:p-6">
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Fasilitas Tersedia</h3>
                        <p class="text-xs text-gray-400">Pilih fasilitas agar peserta tahu apa saja yang disediakan.</p>
                    </div>
                    <span class="inline-flex flex-shrink-0 items-center rounded-full bg-brand-50 px-2.5 py-1 text-[11px] font-bold text-brand-600"><span id="facility-count">0</span>&nbsp;Dipilih</span>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    @foreach($facilityOptions as $facilityKey => $facilityOption)
                        <div class="facility-option rounded-2xl border p-3 sm:p-4 transition-all" data-key="{{ $facilityKey }}" data-label="{{ $facilityOption['label'] }}">
                            <label class="flex cursor-pointer items-start justify-between gap-2">
                                <span class="min-w-0">
                                    <svg class="facility-icon w-5 h-5 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $facilityOption['icon'] !!}</svg>
                                    <span class="block text-sm font-bold leading-tight">{{ $facilityOption['label'] }}</span>
                                </span>
                                <input type="checkbox" class="facility-checkbox sr-only" data-key="{{ $facilityKey }}">
                                <span class="facility-check flex w-5 h-5 flex-shrink-0 items-center justify-center rounded-full">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </span>
                            </label>
                            <input type="text" maxlength="255" class="facility-note mt-2 hidden w-full rounded-lg border-0 bg-white/95 px-2.5 py-1.5 text-xs text-gray-800 placeholder-gray-400 focus:ring-2 focus:ring-lime-400" placeholder="Catatan (opsional)">
                        </div>
                    @endforeach
                </div>
                <div id="facilities-inputs"></div>
                @error('facilities') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
                @error('facilities.*.key') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
        </section>

        {{-- ═════════════ LANGKAH 2 ═════════════ --}}
        <section class="step-panel" data-panel="2" hidden>
            <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_340px] gap-5 items-start">
                <div class="pro-card p-4 sm:p-6 min-w-0">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Posisi & Harga</h3>
                            <p class="text-xs text-gray-400">Atur kuota slot dan biaya untuk setiap posisi.</p>
                        </div>
                        <span class="inline-flex flex-shrink-0 items-center rounded-full bg-lime-400 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-brand-900">Harga per posisi</span>
                    </div>

                    @error('roles') <p class="mb-3 text-xs text-rose-600">{{ $message }}</p> @enderror
                    @if($errors->has('roles.*'))
                        <p class="mb-3 text-xs text-rose-600">{{ $errors->first('roles.*') }}</p>
                    @endif
                    <p id="roles-empty-error" class="hidden mb-3 text-xs text-rose-600">Tambahkan minimal satu posisi.</p>

                    <div id="custom-roles-list" class="space-y-3"></div>

                    <button type="button" id="add-role-button" class="mt-3 flex w-full items-center gap-3 rounded-2xl border-2 border-dashed border-brand-200 bg-brand-50/40 px-4 py-3.5 text-left hover:border-brand-500 hover:bg-brand-50 transition-all">
                        <span class="flex w-8 h-8 flex-shrink-0 items-center justify-center rounded-full bg-brand-500 text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        </span>
                        <span>
                            <span class="block text-sm font-bold text-gray-900">Tambah Posisi</span>
                            <span class="block text-[11px] text-gray-500">Mis. Kiper, Pemain, Cadangan</span>
                        </span>
                    </button>

                    {{-- Mode pembayaran --}}
                    <div class="mt-6 border-t border-gray-100 pt-5">
                        <h3 class="text-sm font-bold text-gray-900">Alur Pembayaran</h3>
                        <p class="text-xs text-gray-400 mb-3">Tentukan kapan peserta dianggap terdaftar.</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($paymentModes as $modeKey => $mode)
                                <label class="payment-mode-option relative flex cursor-pointer gap-3 rounded-2xl border p-4 transition-all" data-mode="{{ $modeKey }}">
                                    <input type="radio" name="payment_mode" value="{{ $modeKey }}" class="sr-only" {{ $paymentMode === $modeKey ? 'checked' : '' }}>
                                    <span class="mode-icon flex w-9 h-9 flex-shrink-0 items-center justify-center rounded-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $mode['icon'] !!}</svg>
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block text-sm font-bold text-gray-900">{{ $mode['title'] }}</span>
                                        <span class="mt-0.5 block text-[11px] leading-relaxed text-gray-500">{{ $mode['desc'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('payment_mode') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Proyeksi --}}
                <aside class="lg:sticky lg:top-6 rounded-2xl bg-brand-900 p-5 text-white shadow-xl">
                    <div class="flex items-center justify-between mb-4">
                        <p class="flex items-center gap-2 text-sm font-bold"><span class="w-2 h-2 rounded-full bg-lime-400"></span>Proyeksi Sesi</p>
                        <span class="rounded-md bg-white/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white/70">Real-time</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-white/5 border border-white/10 p-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-white/50">Jumlah Posisi</p>
                            <p id="proj-tiers" class="text-2xl font-bold mt-1">0</p>
                        </div>
                        <div class="rounded-xl bg-white/5 border border-white/10 p-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-white/50">Total Kapasitas</p>
                            <p class="mt-1"><span id="custom-total-slots" class="text-2xl font-bold">0</span> <span class="text-xs text-white/50">slot</span></p>
                        </div>
                    </div>

                    <p class="mt-4 mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-white/50">Distribusi Kuota</p>
                    <div id="proj-bar" class="flex h-2 w-full overflow-hidden rounded-full bg-white/10 gap-0.5"></div>

                    <div class="mt-4 rounded-xl bg-white/5 border border-white/10 p-4">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-white/50">Potensi Pendapatan Maksimum</p>
                        <p id="custom-total-cost" class="text-3xl font-bold text-lime-400 mt-1 break-words">Rp 0</p>
                        <p class="text-[11px] text-white/40 mt-0.5">Jika semua slot terisi</p>
                    </div>

                    <div class="mt-4 rounded-xl bg-black/20 p-4">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-white/50 mb-2">Rincian per Posisi</p>
                        <div id="proj-breakdown" class="space-y-1.5 text-xs"></div>
                    </div>

                    <div id="container-metode-pembayaran" class="mt-4 flex items-start gap-2 rounded-xl border border-white/10 p-3 text-xs text-white/70">
                        <svg class="w-4 h-4 flex-shrink-0 text-lime-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <span>Pembayaran via <strong class="text-white">Online Banking (Midtrans QRIS)</strong>, terverifikasi otomatis.</span>
                    </div>
                    <div id="container-gratis-badge" class="hidden mt-4 rounded-xl border border-lime-400/30 bg-lime-400/10 p-3 text-xs text-lime-400">
                        <strong>Gratis</strong> · Total biaya Rp 0, peserta mendaftar tanpa pembayaran.
                    </div>
                </aside>
            </div>
        </section>

        {{-- ═════════════ LANGKAH 3 ═════════════ --}}
        <section class="step-panel space-y-5" data-panel="3" hidden>
            <div class="pro-card overflow-hidden">
                <div id="review-hero" class="relative bg-brand-900 bg-cover bg-center px-5 py-7 sm:px-8 sm:py-9 text-white">
                    <div class="absolute inset-0 bg-gradient-to-r from-brand-900 via-brand-900/90 to-brand-900/60"></div>
                    <div class="relative">
                        <div class="flex flex-wrap gap-2 mb-3">
                            <span class="rounded-md bg-lime-400 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-brand-900">Siap Dipublikasi</span>
                            <span class="rounded-md bg-brand-500 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider">Pendaftaran Publik</span>
                        </div>
                        <h2 id="review-title" class="text-2xl sm:text-3xl font-bold break-words">—</h2>
                        <div class="mt-4 flex flex-col sm:flex-row sm:flex-wrap gap-2 sm:gap-5 text-xs sm:text-sm text-white/80">
                            <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-lime-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg><span id="review-date">—</span></span>
                            <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-lime-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span id="review-time">—</span></span>
                            <span class="flex items-center gap-1.5 min-w-0"><svg class="w-4 h-4 flex-shrink-0 text-lime-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span id="review-place" class="truncate">—</span></span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-[minmax(0,2fr)_minmax(0,3fr)] gap-6 p-5 sm:p-6">
                    <div class="space-y-5 min-w-0">
                        <div>
                            <p class="text-sm font-bold text-gray-900 mb-2">Penyelenggara</p>
                            <div class="flex items-center gap-3 rounded-xl bg-gray-50 p-3">
                                <span class="flex w-10 h-10 flex-shrink-0 items-center justify-center rounded-full bg-brand-900 text-sm font-bold text-lime-400">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name ?? 'A', 0, 2)) }}</span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                                    <p class="text-[11px] text-gray-400">Admin Event</p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900 mb-2">Fasilitas</p>
                            <div id="review-facilities" class="flex flex-wrap gap-2"></div>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900 mb-2">Pendaftaran</p>
                            <div class="space-y-1.5 text-xs text-gray-600">
                                <p class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span><span id="review-payment-mode">—</span></p>
                                <p class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span><span id="review-waitlist">—</span></p>
                            </div>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm font-bold text-gray-900">Slot Posisi</p>
                            <span class="rounded-full bg-brand-500 px-2.5 py-0.5 text-[11px] font-bold text-white"><span id="review-total-slots">0</span> Slot</span>
                        </div>
                        <div id="review-roles" class="space-y-2"></div>
                    </div>
                </div>
            </div>

            {{-- Privasi --}}
            <div class="pro-card p-4 sm:p-6">
                <h3 class="text-sm font-bold text-gray-900">Pengaturan Pendaftaran</h3>
                <p class="text-xs text-gray-400 mb-4">Atur antrean dan informasi peserta yang terlihat oleh pengunjung.</p>
                <div class="space-y-3">
                    <input type="hidden" name="enable_waiting_list" value="0">
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-100 hover:border-brand-200 hover:bg-brand-50/30 transition-all cursor-pointer">
                        <input type="checkbox" name="enable_waiting_list" id="enable_waiting_list" value="1"
                               class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                               {{ $waitingListOn ? 'checked' : '' }}>
                        <span>
                            <span class="block text-sm font-medium text-gray-800">Aktifkan Waiting List</span>
                            <span class="block text-xs text-gray-400 mt-0.5">Saat slot penuh, peserta bisa masuk antrean dan dihubungi bila ada slot yang kosong.</span>
                        </span>
                    </label>

                    <input type="hidden" name="show_joined_players_public" value="0">
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-100 hover:border-brand-200 hover:bg-brand-50/30 transition-all cursor-pointer">
                        <input type="checkbox" name="show_joined_players_public" id="show_joined_players_public" value="1"
                               class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                               {{ old('show_joined_players_public', $draftShowPlayers) == '1' ? 'checked' : '' }}>
                        <span>
                            <span class="block text-sm font-medium text-gray-800">Tampilkan daftar peserta yang sudah join ke publik</span>
                            <span class="block text-xs text-gray-400 mt-0.5">Pengunjung halaman join bisa melihat siapa saja yang sudah masuk.</span>
                        </span>
                    </label>
                    <input type="hidden" name="show_joined_player_contacts_public" value="0">
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-100 hover:border-brand-200 hover:bg-brand-50/30 transition-all cursor-pointer">
                        <input type="checkbox" name="show_joined_player_contacts_public" id="show_joined_player_contacts_public" value="1"
                               class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                               {{ old('show_joined_player_contacts_public', $draftShowContacts) == '1' ? 'checked' : '' }}>
                        <span>
                            <span class="block text-sm font-medium text-gray-800">Tampilkan kontak peserta</span>
                            <span class="block text-xs text-gray-400 mt-0.5">Jika nonaktif, hanya nama peserta yang ditampilkan.</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="flex flex-col gap-4 rounded-2xl border border-brand-100 bg-brand-50/50 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                <div class="flex items-start gap-3">
                    <span class="flex w-10 h-10 flex-shrink-0 items-center justify-center rounded-full bg-white text-brand-500 border border-brand-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    </span>
                    <div>
                        <p class="text-sm font-bold text-gray-900">Siap menerima peserta</p>
                        <p class="text-xs text-gray-500">Link pendaftaran & kode join dibuat otomatis setelah event dipublikasikan.</p>
                    </div>
                </div>
                <button type="submit" class="inline-flex w-full sm:w-auto flex-shrink-0 items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-500/25 hover:bg-brand-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Publikasikan Event
                </button>
            </div>
        </section>

        {{-- Footer navigasi --}}
        <div class="sticky bottom-0 z-20 mt-5 -mx-1 px-1 pb-1">
            <div class="flex items-center justify-between gap-2 rounded-2xl border border-gray-200 bg-white/95 backdrop-blur p-2.5 sm:p-3 shadow-lg">
                <button type="button" id="btn-prev" class="inline-flex items-center gap-1.5 rounded-xl bg-gray-100 px-3 sm:px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-200 disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span class="hidden sm:inline">Kembali</span>
                </button>
                <p class="hidden md:flex items-center gap-2 text-xs text-gray-500 min-w-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand-500 flex-shrink-0"></span>
                    <span id="footer-step-label" class="truncate">Langkah 1 dari 3</span>
                </p>
                <div class="flex items-center gap-2">
                    <button type="button" data-action="save-draft" class="hidden sm:inline-flex rounded-xl bg-gray-100 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-200">Simpan Draf</button>
                    <button type="button" id="btn-next" class="inline-flex items-center gap-1.5 rounded-xl bg-brand-500 px-4 sm:px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-500/25 hover:bg-brand-600">
                        <span id="btn-next-label">Lanjut</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const CONFIG = {
        storeUrl: @json(route('admin.events.drafts.store')),
        updateUrlTemplate: @json(route('admin.events.drafts.update', ['draft' => '__ID__'])),
        destroyUrlTemplate: @json(route('admin.events.drafts.destroy', ['draft' => '__ID__'])),
        createUrl: @json(route('admin.events.create')),
        indexUrl: @json(route('admin.events.index')),
        csrf: @json(csrf_token()),
        draft: @json($draftState),
        hasOld: @json(session()->hasOldInput()),
        oldRoles: @json(old('roles', [])),
        oldFacilities: @json(old('facilities', [])),
        errorStep: @json($errorStep),
    };
    const STEP_LABELS = {
        1: 'Langkah 1 dari 3: Detail Event & Fasilitas',
        2: 'Langkah 2 dari 3: Posisi & Harga',
        3: 'Langkah 3 dari 3: Review & Publikasi',
    };
    const ROLE_COLORS = ['#ABD600', '#3377FF', '#99BBFF', '#F59E0B', '#10B981', '#EC4899', '#8B5CF6', '#F97316'];

    const form = document.getElementById('event-form');
    const $ = (id) => document.getElementById(id);
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const formatRupiah = (v) => 'Rp ' + Math.round(v || 0).toLocaleString('id-ID');

    let currentStep = 1;
    let submitting = false;

    // ─── Stepper ─────────────────────────────────────────────
    const tabs = [...document.querySelectorAll('.step-tab')];
    const panels = [...document.querySelectorAll('.step-panel')];

    function renderStepper() {
        tabs.forEach((tab) => {
            const n = Number(tab.dataset.step);
            const active = n === currentStep;
            const done = n < currentStep;
            tab.classList.toggle('bg-brand-500', active);
            tab.classList.toggle('text-white', active);
            tab.classList.toggle('shadow-lg', active);
            tab.classList.toggle('shadow-brand-500/25', active);
            tab.classList.toggle('bg-gray-50', !active);
            tab.classList.toggle('text-gray-800', !active);
            tab.classList.toggle('hover:bg-gray-100', !active);
            const icon = tab.querySelector('.step-icon');
            icon.className = 'step-icon hidden sm:flex w-9 h-9 flex-shrink-0 rounded-lg items-center justify-center ' +
                (active ? 'bg-white/15 text-white' : done ? 'bg-lime-400 text-brand-900' : 'bg-white text-gray-400 border border-gray-200');
            tab.querySelector('.step-kicker').className = 'step-kicker block text-[9px] sm:text-[10px] font-bold uppercase tracking-wider ' + (active ? 'text-white/70' : done ? 'text-lime-600' : 'text-gray-400');
            tab.querySelector('.step-sub').className = 'step-sub hidden md:block text-[11px] truncate ' + (active ? 'text-white/70' : 'text-gray-400');
        });
        panels.forEach((p) => { p.hidden = Number(p.dataset.panel) !== currentStep; });
        $('btn-prev').disabled = currentStep === 1;
        $('btn-next-label').textContent = currentStep === 3 ? 'Publikasikan Event' : (currentStep === 1 ? 'Lanjut: Posisi & Harga' : 'Lanjut: Review');
        $('footer-step-label').textContent = STEP_LABELS[currentStep];
    }

    function goTo(step, { scroll = true } = {}) {
        currentStep = Math.min(3, Math.max(1, step));
        if (currentStep === 3) renderReview();
        renderStepper();
        if (scroll) window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Validasi langkah: tampilkan panel lalu laporkan field pertama yang tidak valid
    function validateStep(step) {
        const panel = panels.find((p) => Number(p.dataset.panel) === step);
        if (step === 2) {
            const empty = !customRolesList.children.length;
            $('roles-empty-error').classList.toggle('hidden', !empty);
            if (empty) { goTo(2); return false; }
        }
        const invalid = [...panel.querySelectorAll('input')].find((el) => !el.checkValidity());
        if (invalid) {
            goTo(step, { scroll: false });
            invalid.reportValidity();
            invalid.focus();
            return false;
        }
        return true;
    }

    tabs.forEach((tab) => tab.addEventListener('click', () => {
        const target = Number(tab.dataset.step);
        for (let s = 1; s < target; s++) {
            if (!validateStep(s)) return;
        }
        goTo(target);
        scheduleSave(0);
    }));
    $('btn-prev').addEventListener('click', () => { goTo(currentStep - 1); scheduleSave(0); });
    $('btn-next').addEventListener('click', () => {
        if (currentStep === 3) { form.requestSubmit(); return; }
        if (!validateStep(currentStep)) return;
        goTo(currentStep + 1);
        scheduleSave(0);
    });

    form.addEventListener('submit', (e) => {
        for (let s = 1; s <= 3; s++) {
            if (!validateStep(s)) { e.preventDefault(); return; }
        }
        submitting = true;
        clearTimeout(saveTimer);
    });

    // ─── Langkah 1: nama, tanggal, banner ────────────────────
    const namaInput = $('nama_event');
    const syncCounter = () => { $('nama-counter').textContent = namaInput.value.length; };
    namaInput.addEventListener('input', syncCounter);

    const dateInput = $('tanggal');
    const timeInput = $('waktu');
    (function initDateTime() {
        const now = new Date();
        const pad = (n) => String(n).padStart(2, '0');
        const today = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
        dateInput.setAttribute('min', today);
        if (!dateInput.value) dateInput.value = today;
        if (!timeInput.value) timeInput.value = `${pad(now.getHours())}:${pad(now.getMinutes())}`;
    })();

    const bannerInput = $('banner_image');
    const bannerDropZone = $('banner-drop-zone');
    let bannerDirty = false;   // file baru belum terkirim ke draf
    let removeBanner = false;  // banner draf dihapus pengguna
    let bannerUrl = CONFIG.draft?.banner_url || null;

    function showBannerPreview(src, name) {
        $('banner-preview-img').src = src;
        $('banner-filename').textContent = name || '';
        $('banner-placeholder').classList.add('hidden');
        $('banner-preview-container').classList.remove('hidden');
        $('banner-actions').classList.remove('hidden');
        bannerDropZone.classList.remove('border-dashed');
        bannerDropZone.classList.add('border-solid', 'border-brand-500');
    }
    function resetBannerPreview() {
        $('banner-preview-img').src = '';
        $('banner-filename').textContent = '';
        $('banner-placeholder').classList.remove('hidden');
        $('banner-preview-container').classList.add('hidden');
        $('banner-actions').classList.add('hidden');
        bannerDropZone.classList.remove('border-solid', 'border-brand-500');
        bannerDropZone.classList.add('border-dashed');
    }

    bannerInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file terlalu besar! Maksimal 2MB.');
            this.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = (e) => { bannerUrl = e.target.result; showBannerPreview(bannerUrl, file.name); };
        reader.readAsDataURL(file);
        bannerDirty = true;
        removeBanner = false;
    });
    $('btn-remove-banner').addEventListener('click', () => {
        bannerInput.value = '';
        bannerUrl = null;
        bannerDirty = false;
        removeBanner = true;
        resetBannerPreview();
        scheduleSave();
    });
    ['dragenter', 'dragover'].forEach((ev) => bannerDropZone.addEventListener(ev, () => bannerDropZone.classList.add('border-brand-500', 'bg-brand-50/20')));
    ['dragleave', 'drop'].forEach((ev) => bannerDropZone.addEventListener(ev, () => bannerDropZone.classList.remove('bg-brand-50/20')));

    // ─── Fasilitas ───────────────────────────────────────────
    const facilityOptions = [...document.querySelectorAll('.facility-option')];
    const facilitiesInputs = $('facilities-inputs');

    function syncFacilityOptionState(option) {
        const checked = option.querySelector('.facility-checkbox').checked;
        option.className = 'facility-option rounded-2xl border p-3 sm:p-4 transition-all ' +
            (checked ? 'border-brand-500 bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'border-gray-200 bg-gray-50 text-gray-800 hover:border-brand-200');
        option.querySelector('.facility-icon').classList.toggle('text-gray-500', !checked);
        option.querySelector('.facility-check').className = 'facility-check flex w-5 h-5 flex-shrink-0 items-center justify-center rounded-full ' +
            (checked ? 'bg-lime-400 text-brand-900' : 'border border-gray-300 text-transparent');
        option.querySelector('.facility-note').classList.toggle('hidden', !checked);
    }

    function refreshFacilityInputs() {
        facilitiesInputs.innerHTML = '';
        let index = 0;
        facilityOptions.forEach((option) => {
            if (!option.querySelector('.facility-checkbox').checked) return;
            [['key', option.dataset.key], ['note', option.querySelector('.facility-note').value]].forEach(([field, value]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `facilities[${index}][${field}]`;
                input.value = value;
                facilitiesInputs.appendChild(input);
            });
            index++;
        });
        $('facility-count').textContent = index;
    }

    function initFacilityOptions(existing) {
        facilityOptions.forEach((option) => {
            const checkbox = option.querySelector('.facility-checkbox');
            const note = option.querySelector('.facility-note');
            const found = (existing || []).find((f) => f && f.key === option.dataset.key);
            if (found) { checkbox.checked = true; note.value = found.note ?? ''; }
            syncFacilityOptionState(option);
            checkbox.addEventListener('change', () => { syncFacilityOptionState(option); refreshFacilityInputs(); });
            note.addEventListener('input', refreshFacilityInputs);
        });
        refreshFacilityInputs();
    }

    // ─── Langkah 2: posisi ───────────────────────────────────
    const customRolesList = $('custom-roles-list');

    function createRoleRow(role = {}, afterRow = null) {
        const row = document.createElement('div');
        row.className = 'role-row rounded-2xl border border-gray-200 bg-gray-50/70 p-3 sm:p-4';
        row.innerHTML = `
            <div class="flex items-center gap-3">
                <span class="role-initial flex w-9 h-9 flex-shrink-0 items-center justify-center rounded-lg bg-brand-900 text-sm font-bold text-lime-400">?</span>
                <input type="text" data-field="name" required maxlength="255" placeholder="Nama posisi, mis. Kiper"
                       class="min-w-0 flex-1 rounded-lg border border-transparent bg-transparent px-2 py-1.5 text-sm font-bold text-gray-900 placeholder-gray-400 hover:border-gray-200 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/10">
                <button type="button" data-role-action="duplicate" title="Duplikat" class="flex w-8 h-8 flex-shrink-0 items-center justify-center rounded-lg text-gray-400 hover:bg-white hover:text-brand-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </button>
                <button type="button" data-role-action="remove" title="Hapus" class="flex w-8 h-8 flex-shrink-0 items-center justify-center rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-gray-500">Kuota (slot)</label>
                    <div class="flex items-center rounded-xl border border-gray-200 bg-white focus-within:border-brand-500">
                        <button type="button" data-role-action="minus" class="w-9 h-10 flex-shrink-0 text-lg font-bold text-gray-400 hover:text-brand-500">−</button>
                        <input type="number" data-field="slots" required min="1" max="10000" step="1"
                               class="w-full min-w-0 border-0 bg-transparent p-0 text-center text-sm font-bold text-gray-900 focus:ring-0">
                        <button type="button" data-role-action="plus" class="w-9 h-10 flex-shrink-0 text-lg font-bold text-gray-400 hover:text-brand-500">+</button>
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-gray-500">Biaya / peserta</label>
                    <div class="flex h-10 items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 focus-within:border-brand-500">
                        <span class="text-xs font-semibold text-gray-400">Rp</span>
                        <input type="number" data-field="price" required min="0" step="any" placeholder="0"
                               class="w-full min-w-0 border-0 bg-transparent p-0 text-sm font-bold text-gray-900 focus:ring-0">
                    </div>
                </div>
            </div>`;

        // Nilai diset lewat properti (bukan template) agar aman dari injeksi HTML
        row.querySelector('[data-field="name"]').value = role.name ?? '';
        row.querySelector('[data-field="slots"]').value = Math.max(1, parseInt(role.slots) || 1);
        row.querySelector('[data-field="price"]').value = role.price ?? 0;

        if (afterRow) afterRow.after(row); else customRolesList.appendChild(row);
        reindexRoles();
        refreshCustomTotals();
        return row;
    }

    function reindexRoles() {
        [...customRolesList.children].forEach((row, i) => {
            row.querySelectorAll('[data-field]').forEach((input) => { input.name = `roles[${i}][${input.dataset.field}]`; });
        });
    }

    function readRoles() {
        return [...customRolesList.children].map((row) => ({
            row,
            name: row.querySelector('[data-field="name"]').value.trim(),
            slots: parseInt(row.querySelector('[data-field="slots"]').value) || 0,
            price: parseFloat(row.querySelector('[data-field="price"]').value) || 0,
        }));
    }

    customRolesList.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-role-action]');
        if (!btn) return;
        const row = btn.closest('.role-row');
        const slotsInput = row.querySelector('[data-field="slots"]');
        switch (btn.dataset.roleAction) {
            case 'remove':
                row.remove();
                reindexRoles();
                break;
            case 'duplicate': {
                const r = readRoles().find((x) => x.row === row);
                createRoleRow({ name: r.name ? r.name + ' (salinan)' : '', slots: r.slots, price: r.price }, row);
                break;
            }
            case 'minus':
                slotsInput.value = Math.max(1, (parseInt(slotsInput.value) || 1) - 1);
                break;
            case 'plus':
                slotsInput.value = Math.min(10000, (parseInt(slotsInput.value) || 0) + 1);
                break;
        }
        refreshCustomTotals();
        scheduleSave();
    });

    $('add-role-button').addEventListener('click', () => {
        const row = createRoleRow({ name: '', slots: 1, price: 0 });
        row.querySelector('[data-field="name"]').focus();
        $('roles-empty-error').classList.add('hidden');
        scheduleSave();
    });

    function refreshCustomTotals() {
        const roles = readRoles();
        const totalSlots = roles.reduce((s, r) => s + r.slots, 0);
        const totalCost = roles.reduce((s, r) => s + r.slots * r.price, 0);

        roles.forEach((r) => { r.row.querySelector('.role-initial').textContent = (r.name.charAt(0) || '?').toUpperCase(); });

        $('proj-tiers').textContent = roles.length;
        $('custom-total-slots').textContent = totalSlots;
        $('custom-total-cost').textContent = formatRupiah(totalCost);

        $('proj-bar').innerHTML = totalSlots > 0
            ? roles.filter((r) => r.slots > 0).map((r, i) => `<span style="width:${(r.slots / totalSlots) * 100}%;background:${ROLE_COLORS[i % ROLE_COLORS.length]}"></span>`).join('')
            : '';

        $('proj-breakdown').innerHTML = roles.length
            ? roles.map((r, i) => `
                <div class="flex items-center justify-between gap-2">
                    <span class="flex min-w-0 items-center gap-2 text-white/70"><span class="w-2 h-2 flex-shrink-0 rounded-full" style="background:${ROLE_COLORS[i % ROLE_COLORS.length]}"></span><span class="truncate">${esc(r.name || 'Tanpa nama')} (${r.slots} × ${formatRupiah(r.price)})</span></span>
                    <span class="flex-shrink-0 font-bold">${formatRupiah(r.slots * r.price)}</span>
                </div>`).join('')
            : '<p class="text-white/40">Belum ada posisi.</p>';

        const isFree = totalCost <= 0;
        $('container-metode-pembayaran').classList.toggle('hidden', isFree);
        $('container-gratis-badge').classList.toggle('hidden', !isFree);
    }

    // ─── Langkah 3: review & privasi ─────────────────────────
    function renderReview() {
        $('review-title').textContent = namaInput.value.trim() || 'Tanpa judul';
        const d = dateInput.value ? new Date(dateInput.value + 'T00:00:00') : null;
        $('review-date').textContent = d ? d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) : '—';
        $('review-time').textContent = timeInput.value ? timeInput.value.slice(0, 5) + ' WIB' : '—';
        $('review-place').textContent = $('tempat').value.trim() || '—';
        $('review-hero').style.backgroundImage = bannerUrl ? `url("${bannerUrl}")` : '';

        const chosen = facilityOptions.filter((o) => o.querySelector('.facility-checkbox').checked);
        $('review-facilities').innerHTML = chosen.length
            ? chosen.map((o) => {
                const note = o.querySelector('.facility-note').value.trim();
                return `<span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700">
                    <svg class="w-3.5 h-3.5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    ${esc(o.dataset.label)}${note ? `<span class="font-normal text-gray-400">· ${esc(note)}</span>` : ''}</span>`;
            }).join('')
            : '<p class="text-xs text-gray-400">Tidak ada fasilitas dipilih.</p>';

        $('review-payment-mode').textContent = MODE_LABELS[selectedMode()];
        $('review-waitlist').textContent = $('enable_waiting_list').checked ? 'Waiting list aktif saat slot penuh' : 'Tanpa waiting list';

        const roles = readRoles();
        $('review-total-slots').textContent = roles.reduce((s, r) => s + r.slots, 0);
        $('review-roles').innerHTML = roles.map((r) => `
            <div class="flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50 p-3">
                <span class="flex w-8 h-8 flex-shrink-0 items-center justify-center rounded-lg bg-brand-900 text-xs font-bold text-lime-400">${esc((r.name.charAt(0) || '?').toUpperCase())}</span>
                <p class="min-w-0 flex-1 truncate text-sm font-bold text-gray-900">${esc(r.name || 'Tanpa nama')}</p>
                <div class="flex-shrink-0 text-right">
                    <p class="text-sm font-bold text-brand-600">${r.price > 0 ? formatRupiah(r.price) : 'Gratis'}</p>
                    <p class="text-[11px] text-gray-400">${r.slots} slot</p>
                </div>
            </div>`).join('') || '<p class="text-xs text-gray-400">Belum ada posisi.</p>';
    }

    // ─── Alur pembayaran & waiting list ──────────────────────
    const modeOptions = [...document.querySelectorAll('.payment-mode-option')];
    const MODE_LABELS = {
        pay_later: 'Daftar dulu, link pelunasan dikirim via WhatsApp',
        pay_first: 'Bayar dulu baru terdaftar (slot ditahan {{ \App\Models\Event::HOLD_MINUTES }} menit)',
    };
    const selectedMode = () => (form.querySelector('input[name="payment_mode"]:checked') || {}).value || 'pay_later';
    function syncModeOptions() {
        const mode = selectedMode();
        modeOptions.forEach((opt) => {
            const active = opt.dataset.mode === mode;
            opt.className = 'payment-mode-option relative flex cursor-pointer gap-3 rounded-2xl border p-4 transition-all ' +
                (active ? 'border-brand-500 bg-brand-50/60 ring-2 ring-brand-500/15' : 'border-gray-200 bg-white hover:border-brand-200');
            opt.querySelector('.mode-icon').className = 'mode-icon flex w-9 h-9 flex-shrink-0 items-center justify-center rounded-lg ' +
                (active ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-400');
        });
    }
    modeOptions.forEach((opt) => opt.querySelector('input').addEventListener('change', syncModeOptions));
    if (!form.querySelector('input[name="payment_mode"]:checked')) {
        form.querySelector('input[name="payment_mode"][value="pay_later"]').checked = true;
    }
    syncModeOptions();

    const playersToggle = $('show_joined_players_public');
    const contactToggle = $('show_joined_player_contacts_public');
    function syncToggles() {
        contactToggle.disabled = !playersToggle.checked;
        if (!playersToggle.checked) contactToggle.checked = false;
    }
    playersToggle.addEventListener('change', syncToggles);

    // ─── Draf & auto-save ────────────────────────────────────
    const draftIdInput = $('draft_id');
    const statusEl = $('autosave-status');
    let saveTimer = null;
    let saving = false;
    let pending = false;
    let savedAt = CONFIG.draft?.saved_at ? new Date(CONFIG.draft.saved_at) : null;

    function relativeTime(date) {
        const mins = Math.floor((Date.now() - date.getTime()) / 60000);
        if (mins < 1) return 'baru saja';
        if (mins < 60) return `${mins} menit lalu`;
        const hours = Math.floor(mins / 60);
        if (hours < 24) return `${hours} jam lalu`;
        return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
    }
    function setStatus(text, tone = 'muted') {
        statusEl.textContent = text;
        statusEl.className = 'text-xs ' + ({ muted: 'text-gray-400', ok: 'text-emerald-600', err: 'text-rose-600' }[tone]);
    }
    function renderSavedStatus() {
        if (savedAt && !saving) setStatus('Tersimpan otomatis ' + relativeTime(savedAt));
    }
    setInterval(renderSavedStatus, 30000);

    function hasContent() {
        return namaInput.value.trim() !== '' || $('tempat').value.trim() !== ''
            || readRoles().some((r) => r.name !== '') || $('facility-count').textContent !== '0'
            || bannerDirty || !!draftIdInput.value;
    }

    function scheduleSave(delay = 1500) {
        if (submitting) return;
        clearTimeout(saveTimer);
        saveTimer = setTimeout(saveDraft, delay);
    }

    async function saveDraft({ manual = false } = {}) {
        clearTimeout(saveTimer);
        if (submitting) return;
        if (!hasContent()) {
            if (manual) setStatus('Isi minimal nama event untuk menyimpan draf', 'err');
            return;
        }
        if (saving) { pending = true; return; }
        saving = true;
        setStatus('Menyimpan…');

        const data = new FormData(form);
        data.delete('_token');
        data.delete('draft_id');
        if (!bannerDirty) data.delete('banner_image');
        if (removeBanner) data.append('remove_banner', '1');
        data.append('step', currentStep);

        const id = draftIdInput.value;
        const url = id ? CONFIG.updateUrlTemplate.replace('__ID__', id) : CONFIG.storeUrl;
        const sentBanner = bannerDirty;

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CONFIG.csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: data,
            });
            if (!res.ok) throw new Error(res.status === 422 ? 'invalid' : 'http');
            const json = await res.json();
            draftIdInput.value = json.id;
            if (sentBanner) { bannerDirty = false; bannerInput.value = ''; }
            removeBanner = false;
            savedAt = new Date(json.saved_at);
            const url2 = new URL(window.location.href);
            if (url2.searchParams.get('draft') !== String(json.id)) {
                url2.searchParams.set('draft', json.id);
                history.replaceState(null, '', url2);
            }
            saving = false;
            if (manual) setStatus('Draf tersimpan', 'ok'); else renderSavedStatus();
        } catch (err) {
            saving = false;
            setStatus(err.message === 'invalid' ? 'Gagal menyimpan draf (cek isian)' : 'Gagal menyimpan draf, coba lagi', 'err');
        }
        if (pending) { pending = false; saveDraft(); }
    }

    form.addEventListener('input', (e) => {
        if (e.target === bannerInput) return;
        refreshCustomTotals();
        scheduleSave();
    });
    form.addEventListener('change', () => scheduleSave());
    document.querySelectorAll('[data-action="save-draft"]').forEach((b) => b.addEventListener('click', () => saveDraft({ manual: true })));
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden' && saveTimer) saveDraft();
    });

    $('btn-discard').addEventListener('click', async () => {
        const id = draftIdInput.value;
        if (!confirm(id ? 'Buang draf ini? Semua isian akan dihapus.' : 'Batalkan pembuatan event? Isian akan hilang.')) return;
        submitting = true;
        clearTimeout(saveTimer);
        if (id) {
            const data = new FormData();
            data.append('_method', 'DELETE');
            try {
                await fetch(CONFIG.destroyUrlTemplate.replace('__ID__', id), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CONFIG.csrf, 'Accept': 'application/json' },
                    body: data,
                });
            } catch (e) { /* tetap keluar dari halaman */ }
        }
        window.location.href = CONFIG.indexUrl;
    });

    // ─── Inisialisasi ────────────────────────────────────────
    const source = CONFIG.hasOld ? { roles: CONFIG.oldRoles, facilities: CONFIG.oldFacilities } : (CONFIG.draft || { roles: [], facilities: [] });
    (source.roles || []).forEach((r) => createRoleRow(r));
    if (!customRolesList.children.length) createRoleRow({ name: '', slots: 1, price: 0 });
    initFacilityOptions(source.facilities);
    syncCounter();
    syncToggles();
    refreshCustomTotals();

    if (bannerUrl) showBannerPreview(bannerUrl, 'Banner dari draf');
    if (savedAt) renderSavedStatus();

    goTo(CONFIG.errorStep || CONFIG.draft?.step || 1, { scroll: false });
})();
</script>
@endpush
