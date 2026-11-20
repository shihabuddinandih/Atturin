@extends('layouts.admin')

@php
    $facilityOptions = \App\Support\FacilityCatalog::options();
    $roles = collect($event->roles ?? []);
    $bannerUrl = $event->banner_image ? asset('storage/' . $event->banner_image) : null;
    $tanggalValue = old('tanggal', $event->tanggal ? $event->tanggal->format('Y-m-d') : '');
    $waktuValue = old('waktu', $event->waktu ? substr($event->waktu, 0, 5) : '');
    $existingFacilities = old('facilities', $event->facilities ?? []);
    $showPlayers = old('show_joined_players_public', $event->show_joined_players_public ? '1' : '0') == '1';
    $showContacts = old('show_joined_player_contacts_public', $event->show_joined_player_contacts_public ? '1' : '0') == '1';
    $waitingListOn = old('enable_waiting_list', $event->enable_waiting_list ? '1' : '0') == '1';
    $paymentModeLabel = $event->requiresPaymentFirst()
        ? 'Bayar dulu baru terdaftar (slot ditahan ' . \App\Models\Event::HOLD_MINUTES . ' menit)'
        : 'Daftar dulu, bayar menyusul';
@endphp

@push('styles')
<style>
    .event-edit .field-input {
        width: 100%; border-radius: 0.75rem; border: 1px solid #E5E7EB; background: #fff;
        padding: 0.625rem 1rem; font-size: 0.875rem; color: #1F2937; transition: all .15s ease;
    }
    .event-edit .field-input:focus { border-color: #0052FF; box-shadow: 0 0 0 3px rgba(0,82,255,.1); outline: none; }
    .event-edit .field-input.has-icon { padding-left: 2.5rem; }
    .event-edit .field-label { display: block; font-size: 0.75rem; font-weight: 600; color: #374151; margin-bottom: 0.375rem; }
</style>
@endpush

@section('content')
<div class="event-edit w-full max-w-4xl mx-auto pb-4 space-y-5">
    {{-- Breadcrumb + Header --}}
    <div>
        <div class="flex flex-wrap items-center gap-2 text-sm text-gray-400 mb-2">
            <a href="{{ route('admin.events.index') }}" class="hover:text-brand-500 transition-colors">Dashboard</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('admin.events.show', $event) }}" class="hover:text-brand-500 transition-colors">Detail Event</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-600">Edit</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Edit Event</h1>
        <p class="text-sm text-gray-500 mt-1">Perbarui detail event. Link pendaftaran yang sudah dibagikan tetap berlaku.</p>
    </div>

    <form id="event-edit-form" action="{{ route('admin.events.update', $event) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PUT')
        <input type="hidden" name="remove_banner" id="remove_banner" value="0">

        {{-- Informasi Event --}}
        <div class="pro-card overflow-hidden">
            <div class="flex items-center gap-3 border-b border-gray-100 px-4 py-4 sm:px-6">
                <span class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Informasi Event</h3>
                    <p class="text-xs text-gray-400">Jadwal dan lokasi pertandingan</p>
                </div>
            </div>

            <div class="p-4 sm:p-6 space-y-5">
                <div>
                    <div class="flex items-center justify-between">
                        <label for="nama_event" class="field-label">Nama Event <span class="text-rose-500">*</span></label>
                        <span class="text-[11px] text-gray-400 mb-1.5"><span id="nama-counter">0</span>/80</span>
                    </div>
                    <input type="text" name="nama_event" id="nama_event" maxlength="80" required
                           value="{{ old('nama_event', $event->nama_event) }}" class="field-input">
                    @error('nama_event') <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="tanggal" class="field-label">Tanggal Event <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <input type="date" name="tanggal" id="tanggal" required value="{{ $tanggalValue }}" class="field-input has-icon">
                        </div>
                        @error('tanggal') <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="waktu" class="field-label">Waktu Kick-off <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <input type="time" name="waktu" id="waktu" required value="{{ $waktuValue }}" class="field-input has-icon">
                        </div>
                        @error('waktu') <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="tempat" class="field-label">Tempat / Venue <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <input type="text" name="tempat" id="tempat" required maxlength="255" value="{{ old('tempat', $event->tempat) }}" class="field-input has-icon">
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
                                <p class="text-[11px] font-semibold text-white/80 uppercase tracking-wider">Preview Banner · klik untuk ganti</p>
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
                    <div class="facility-option rounded-2xl border p-3 sm:p-4 transition-all" data-key="{{ $facilityKey }}">
                        <label class="flex cursor-pointer items-start justify-between gap-2">
                            <span class="min-w-0">
                                <svg class="facility-icon w-5 h-5 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $facilityOption['icon'] !!}</svg>
                                <span class="block text-sm font-bold leading-tight">{{ $facilityOption['label'] }}</span>
                            </span>
                            <input type="checkbox" class="facility-checkbox sr-only">
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

        {{-- Pengaturan Pendaftaran --}}
        <div class="pro-card p-4 sm:p-6">
            <h3 class="text-sm font-bold text-gray-900">Pengaturan Pendaftaran</h3>
            <p class="text-xs text-gray-400 mb-4">Atur antrean dan informasi peserta yang terlihat oleh pengunjung.</p>
            <div class="space-y-3">
                <input type="hidden" name="enable_waiting_list" value="0">
                <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-100 hover:border-brand-200 hover:bg-brand-50/30 transition-all cursor-pointer">
                    <input type="checkbox" name="enable_waiting_list" value="1" class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500" {{ $waitingListOn ? 'checked' : '' }}>
                    <span>
                        <span class="block text-sm font-medium text-gray-800">Aktifkan Waiting List</span>
                        <span class="block text-xs text-gray-400 mt-0.5">Saat slot penuh, peserta bisa masuk antrean dan dihubungi bila ada slot yang kosong.</span>
                    </span>
                </label>

                <input type="hidden" name="show_joined_players_public" value="0">
                <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-100 hover:border-brand-200 hover:bg-brand-50/30 transition-all cursor-pointer">
                    <input type="checkbox" name="show_joined_players_public" id="show_joined_players_public" value="1" class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500" {{ $showPlayers ? 'checked' : '' }}>
                    <span>
                        <span class="block text-sm font-medium text-gray-800">Tampilkan daftar peserta yang sudah join ke publik</span>
                        <span class="block text-xs text-gray-400 mt-0.5">Pengunjung halaman join bisa melihat siapa saja yang sudah masuk.</span>
                    </span>
                </label>

                <input type="hidden" name="show_joined_player_contacts_public" value="0">
                <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-100 hover:border-brand-200 hover:bg-brand-50/30 transition-all cursor-pointer">
                    <input type="checkbox" name="show_joined_player_contacts_public" id="show_joined_player_contacts_public" value="1" class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500" {{ $showContacts ? 'checked' : '' }}>
                    <span>
                        <span class="block text-sm font-medium text-gray-800">Tampilkan kontak peserta</span>
                        <span class="block text-xs text-gray-400 mt-0.5">Jika nonaktif, hanya nama peserta yang ditampilkan.</span>
                    </span>
                </label>
            </div>
        </div>

        {{-- Posisi & Harga (terkunci) --}}
        <div class="pro-card p-4 sm:p-6 bg-gray-50/60">
            <div class="flex items-start gap-3 mb-4">
                <span class="w-10 h-10 flex-shrink-0 rounded-xl bg-gray-200 text-gray-500 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </span>
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Posisi, Harga & Pembayaran <span class="ml-1 rounded-md bg-gray-200 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-gray-600">Terkunci</span></h3>
                    <p class="text-xs text-gray-500 mt-0.5">Posisi, kuota, harga, dan alur pembayaran tidak dapat diubah setelah event dibuat karena terkait pembayaran peserta.</p>
                </div>
            </div>
            <div class="space-y-2">
                @forelse($roles as $role)
                    <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-3">
                        <span class="flex w-8 h-8 flex-shrink-0 items-center justify-center rounded-lg bg-brand-900 text-xs font-bold text-lime-400">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($role['name'] ?? '?', 0, 1)) }}</span>
                        <p class="min-w-0 flex-1 truncate text-sm font-bold text-gray-900">{{ $role['name'] ?? 'Tanpa nama' }}</p>
                        <div class="flex-shrink-0 text-right">
                            <p class="text-sm font-bold text-brand-600">{{ (float) ($role['price'] ?? 0) > 0 ? 'Rp ' . number_format((float) $role['price'], 0, ',', '.') : 'Gratis' }}</p>
                            <p class="text-[11px] text-gray-400">{{ $role['slots'] ?? 0 }} slot</p>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-400">Tidak ada posisi.</p>
                @endforelse
            </div>
            <p class="mt-3 flex items-center gap-2 text-xs text-gray-600">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                Alur pembayaran: {{ $paymentModeLabel }}
            </p>
        </div>

        {{-- Aksi --}}
        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.events.show', $event) }}" class="inline-flex justify-center items-center rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Batal</a>
            <button type="submit" class="inline-flex justify-center items-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-500/25 hover:bg-brand-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const $ = (id) => document.getElementById(id);
    const existingFacilities = @json(array_values((array) $existingFacilities));
    const initialBanner = @json($bannerUrl);

    // ─── Nama counter ───────────────────────────────────────
    const namaInput = $('nama_event');
    const syncCounter = () => { $('nama-counter').textContent = namaInput.value.length; };
    namaInput.addEventListener('input', syncCounter);
    syncCounter();

    // ─── Banner ─────────────────────────────────────────────
    const bannerInput = $('banner_image');
    const dropZone = $('banner-drop-zone');
    const removeInput = $('remove_banner');

    function showPreview(src, name) {
        $('banner-preview-img').src = src;
        $('banner-filename').textContent = name || '';
        $('banner-placeholder').classList.add('hidden');
        $('banner-preview-container').classList.remove('hidden');
        $('banner-actions').classList.remove('hidden');
        dropZone.classList.remove('border-dashed');
        dropZone.classList.add('border-solid', 'border-brand-500');
    }
    function resetPreview() {
        $('banner-preview-img').src = '';
        $('banner-filename').textContent = '';
        $('banner-placeholder').classList.remove('hidden');
        $('banner-preview-container').classList.add('hidden');
        $('banner-actions').classList.add('hidden');
        dropZone.classList.remove('border-solid', 'border-brand-500');
        dropZone.classList.add('border-dashed');
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
        reader.onload = (e) => showPreview(e.target.result, file.name);
        reader.readAsDataURL(file);
        removeInput.value = '0';
    });
    $('btn-remove-banner').addEventListener('click', () => {
        bannerInput.value = '';
        removeInput.value = '1';
        resetPreview();
    });
    if (initialBanner) showPreview(initialBanner, 'Banner saat ini');

    // ─── Fasilitas ──────────────────────────────────────────
    const options = [...document.querySelectorAll('.facility-option')];
    const inputs = $('facilities-inputs');

    function syncOption(option) {
        const checked = option.querySelector('.facility-checkbox').checked;
        option.className = 'facility-option rounded-2xl border p-3 sm:p-4 transition-all ' +
            (checked ? 'border-brand-500 bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'border-gray-200 bg-gray-50 text-gray-800 hover:border-brand-200');
        option.querySelector('.facility-icon').classList.toggle('text-gray-500', !checked);
        option.querySelector('.facility-check').className = 'facility-check flex w-5 h-5 flex-shrink-0 items-center justify-center rounded-full ' +
            (checked ? 'bg-lime-400 text-brand-900' : 'border border-gray-300 text-transparent');
        option.querySelector('.facility-note').classList.toggle('hidden', !checked);
    }

    function refreshInputs() {
        inputs.innerHTML = '';
        let index = 0;
        options.forEach((option) => {
            if (!option.querySelector('.facility-checkbox').checked) return;
            [['key', option.dataset.key], ['note', option.querySelector('.facility-note').value]].forEach(([field, value]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `facilities[${index}][${field}]`;
                input.value = value;
                inputs.appendChild(input);
            });
            index++;
        });
        $('facility-count').textContent = index;
    }

    options.forEach((option) => {
        const checkbox = option.querySelector('.facility-checkbox');
        const note = option.querySelector('.facility-note');
        const found = existingFacilities.find((f) => f && f.key === option.dataset.key);
        if (found) { checkbox.checked = true; note.value = found.note ?? ''; }
        syncOption(option);
        checkbox.addEventListener('change', () => { syncOption(option); refreshInputs(); });
        note.addEventListener('input', refreshInputs);
    });
    refreshInputs();

    // ─── Toggle visibilitas ─────────────────────────────────
    const playersToggle = $('show_joined_players_public');
    const contactToggle = $('show_joined_player_contacts_public');
    function syncToggles() {
        contactToggle.disabled = !playersToggle.checked;
        if (!playersToggle.checked) contactToggle.checked = false;
    }
    playersToggle.addEventListener('change', syncToggles);
    syncToggles();
})();
</script>
@endpush
