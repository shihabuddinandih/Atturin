@extends('layouts.admin')

@section('content')
<div class="w-full max-w-5xl mx-auto">
    {{-- Page Header --}}
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-2">
            <a href="{{ route('admin.events.index') }}" class="hover:text-brand-500 transition-colors">Dashboard</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-600">Buat Event Baru</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Buat Event Baru</h1>
        <p class="text-sm text-gray-500 mt-1">Tentukan jadwal, kapasitas, dan skema iuran sebelum join link dibuka.</p>
    </div>

    <div class="pro-card overflow-hidden">
        <form action="{{ route('admin.events.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Section: Banner Image --}}
            <div class="p-4 sm:p-6 border-b border-gray-100 bg-gray-50/20">
                <div class="flex items-center gap-2 mb-5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Hero Banner Event</h3>
                </div>

                <div class="space-y-4">
                    <div class="relative">
                        {{-- Custom Drag & Drop Area --}}
                        <div id="banner-drop-zone" class="border-2 border-dashed border-gray-200 hover:border-brand-500 rounded-2xl p-6 transition-all duration-300 bg-white hover:bg-brand-50/10 cursor-pointer flex flex-col items-center justify-center min-h-[160px] text-center relative overflow-hidden group">
                            
                            {{-- Input file hidden --}}
                            <input type="file" name="banner_image" id="banner_image" accept="image/jpeg,image/png,image/webp" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

                            {{-- No Image Preview State --}}
                            <div id="banner-placeholder" class="flex flex-col items-center justify-center space-y-3 py-4 transition-all duration-300">
                                <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center text-gray-400 group-hover:scale-110 group-hover:text-brand-500 transition-all duration-300 border border-gray-100">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">Pilih atau Seret gambar hero banner</p>
                                    <p class="text-xs text-gray-400 mt-1">Format JPG, PNG, atau WebP. Ukuran maks. 2MB</p>
                                </div>
                            </div>

                            {{-- Live Image Preview State --}}
                            <div id="banner-preview-container" class="hidden absolute inset-0 w-full h-full bg-gray-900 flex items-center justify-center">
                                <img id="banner-preview-img" src="" alt="Banner Preview" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-black/20 flex flex-col justify-end p-4 text-left">
                                    <p class="text-xs font-semibold text-white/80 uppercase tracking-wider">Preview Banner</p>
                                    <p id="banner-filename" class="text-sm font-bold text-white truncate max-w-[80%]">filename.jpg</p>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div id="banner-actions" class="hidden mt-3 flex items-center gap-3">
                            <button type="button" id="btn-remove-banner" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-red-200 bg-red-50 text-sm font-semibold text-red-700 hover:bg-red-100 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Hapus Gambar
                            </button>
                        </div>

                        @error('banner_image')
                            <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Section: Event Info --}}
            <div class="p-4 sm:p-6 border-b border-gray-100">
                <div class="flex items-center gap-2 mb-5">
                    <div class="w-8 h-8 rounded-lg bg-brand-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Informasi Event</h3>
                </div>

                <div class="space-y-4">
                    {{-- Quick Sport Template removed: use manual inputs only --}}

                    <div>
                        <label for="nama_event" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Event</label>
                        <input type="text" name="nama_event" id="nama_event" value="{{ old('nama_event') }}"
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all"
                               required placeholder="Cth: Friendly Event Weekend">
                        @error('nama_event')
                            <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal</label>
                            <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal') }}"
                                   class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all"
                                   required>
                        </div>
                        <div>
                            <label for="waktu" class="block text-sm font-medium text-gray-700 mb-1.5">Waktu</label>
                            <input type="time" name="waktu" id="waktu" value="{{ old('waktu') }}"
                                   class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all"
                                   required>
                        </div>
                    </div>

                    <div>
                        <label for="tempat" class="block text-sm font-medium text-gray-700 mb-1.5">Tempat (Venue)</label>
                        <input type="text" name="tempat" id="tempat" value="{{ old('tempat') }}"
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all"
                               required placeholder="Cth: Arena Sport Center">
                    </div>

                    <!-- <div>
                        <input type="hidden" name="enable_waiting_list" value="0">
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-100 hover:border-brand-200 hover:bg-brand-50/30 transition-all cursor-pointer">
                            <input type="checkbox" name="enable_waiting_list" id="enable_waiting_list" value="1"
                                   class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                                   {{ old('enable_waiting_list') == '1' ? 'checked' : '' }}>
                            <span>
                                <span class="block text-sm font-medium text-gray-800">Aktifkan Waiting List</span>
                                <span class="block text-xs text-gray-400 mt-0.5">Jika aktif, peserta akan masuk waiting list ketika slot sudah penuh.</span>
                            </span>
                        </label>
                    </div> -->

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Fasilitas Event</label>
                        <p class="text-xs text-gray-400 mb-3">Pilih fasilitas yang tersedia, tambahkan catatan opsional (mis. nama fotografer).</p>
                        <div id="facilities-grid" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach(\App\Support\FacilityCatalog::options() as $facilityKey => $facilityOption)
                                <div class="facility-option rounded-xl border border-gray-200 p-3" data-key="{{ $facilityKey }}">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="checkbox" class="facility-checkbox h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500" data-key="{{ $facilityKey }}">
                                        <span class="w-8 h-8 rounded-lg bg-brand-50 flex items-center justify-center text-brand-500 flex-shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $facilityOption['icon'] !!}</svg>
                                        </span>
                                        <span class="text-sm font-medium text-gray-800">{{ $facilityOption['label'] }}</span>
                                    </label>
                                    <input type="text" class="facility-note mt-2 hidden w-full rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10" placeholder="Catatan (opsional), mis. nama fotografer">
                                </div>
                            @endforeach
                        </div>
                        <div id="facilities-inputs"></div>
                        @error('facilities')
                            <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                        @error('facilities.*.key')
                            <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Section: Payment --}}
            <div class="p-4 sm:p-6 border-b border-gray-100">
                <div class="flex items-center gap-2 mb-5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Pengaturan Pembayaran & Skema Iuran</h3>
                </div>

                <div class="space-y-4">
                    <input type="hidden" name="skema_iuran" value="custom">
                    @error('roles')
                        <p class="text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                    <div class="grid grid-cols-1 xl:grid-cols-[2fr_1fr] gap-6">
                        <div class="space-y-4 min-w-0">
                            <div id="custom-mode-settings" class="space-y-4">
                                <div class="rounded-2xl sm:rounded-3xl border border-gray-200 bg-white p-4 shadow-sm">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">Role Custom</p>
                                            <p class="text-xs text-gray-500">Total biaya event dihitung otomatis dari setiap role.</p>
                                        </div>
                                        <button type="button" id="add-role-button" class="inline-flex items-center justify-center gap-2 rounded-xl border border-brand-500 bg-brand-50 px-3 py-2 text-xs font-semibold text-brand-700 hover:bg-brand-100 transition-all">
                                            + Tambah Role
                                        </button>
                                    </div>
                                </div>
                                <div class="hidden sm:grid sm:grid-cols-[1.5fr_1fr_1fr_0.7fr] gap-3 px-4 py-2 text-xs uppercase tracking-wide text-gray-500 border border-gray-200 rounded-xl bg-gray-50">
                                    <span>Nama Role</span>
                                    <span>Slot Maksimal</span>
                                    <span>Harga / Slot</span>
                                    <span class="sr-only">Aksi</span>
                                </div>
                                <div id="custom-roles-list" class="space-y-3"></div>
                                <div class="rounded-2xl sm:rounded-3xl border border-gray-200 bg-brand-50/60 p-4 text-sm text-gray-700">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p class="text-xs uppercase tracking-wide text-slate-500">Ringkasan Role</p>
                                            <p class="text-sm font-semibold text-gray-900">Total peran dan slot</p>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3 text-sm">
                                            <div class="min-w-0 rounded-xl bg-white p-3 border border-gray-200">
                                                <p class="text-xs text-gray-500">Total slot</p>
                                                <p id="custom-total-slots" class="font-semibold text-gray-900 break-words">0</p>
                                            </div>
                                            <div class="min-w-0 rounded-xl bg-white p-3 border border-gray-200">
                                                <p class="text-xs text-gray-500">Total biaya</p>
                                                <p id="custom-total-cost" class="font-semibold text-gray-900 break-words">Rp 0</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4 min-w-0">
                            <div id="container-metode-pembayaran" class="rounded-2xl sm:rounded-3xl border border-gray-200 bg-blue-50 p-4 shadow-sm">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Metode Pembayaran</label>
                                <input type="hidden" name="metode_pembayaran" value="online_banking">
                                <div class="rounded-xl border border-blue-100 bg-blue-100/80 px-4 py-3 text-sm text-blue-900 font-semibold">
                                    Online Banking (Midtrans QRIS)
                                </div>
                            </div>
                            <div id="container-gratis-badge" class="hidden rounded-2xl sm:rounded-3xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
                                <div class="font-semibold text-emerald-800">Gratis</div>
                                <p class="mt-1 text-xs text-emerald-700">Total biaya diatur Rp 0. Peserta dapat mendaftar tanpa pembayaran.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section: Privacy --}}
            <div class="p-4 sm:p-6 border-b border-gray-100">
                <div class="flex items-center gap-2 mb-5">
                    <div class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Pengaturan Privasi Halaman Join</h3>
                </div>

                <div class="space-y-4">
                    <input type="hidden" name="show_joined_players_public" value="0">
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-100 hover:border-brand-200 hover:bg-brand-50/30 transition-all cursor-pointer">
                        <input type="checkbox" name="show_joined_players_public" id="show_joined_players_public" value="1"
                               class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                               {{ old('show_joined_players_public', '1') == '1' ? 'checked' : '' }}>
                        <span>
                            <span class="block text-sm font-medium text-gray-800">Tampilkan daftar peserta yang sudah join ke publik</span>
                            <span class="block text-xs text-gray-400 mt-0.5">Jika aktif, pengunjung halaman join bisa melihat siapa saja yang sudah masuk.</span>
                        </span>
                    </label>

                    <input type="hidden" name="show_joined_player_contacts_public" value="0">
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-100 hover:border-brand-200 hover:bg-brand-50/30 transition-all cursor-pointer">
                        <input type="checkbox" name="show_joined_player_contacts_public" id="show_joined_player_contacts_public" value="1"
                               class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                               {{ old('show_joined_player_contacts_public') == '1' ? 'checked' : '' }}>
                        <span>
                            <span class="block text-sm font-medium text-gray-800">Tampilkan kontak peserta</span>
                            <span class="block text-xs text-gray-400 mt-0.5">Jika nonaktif, hanya nama peserta yang ditampilkan.</span>
                        </span>
                    </label>
                </div>
            </div>

            {{-- Actions --}}
            <div class="p-4 sm:p-6 bg-gray-50/50 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3">
                <a href="{{ route('admin.events.index') }}" class="px-5 py-2.5 text-center text-sm font-medium text-gray-600 hover:text-gray-800 transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="inline-flex w-full sm:w-auto justify-center items-center gap-2 px-6 py-2.5 rounded-xl bg-lime-400 hover:bg-lime-500 text-brand-900 font-bold text-sm shadow-lg shadow-lime-400/20 transition-all hover:shadow-xl hover:shadow-lime-400/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    Simpan & Generate Link
                </button>
            </div>
        </form>
    </div>
</div>
<input type="hidden" id="old_roles_json" value='@json(old('roles', []))'>
<input type="hidden" id="old_facilities_json" value='@json(old('facilities', []))'>
@endsection

@push('scripts')
<script>
    (function () {
        // Elements for visibilitas public toggles
        const playersToggle = document.getElementById('show_joined_players_public');
        const contactToggle = document.getElementById('show_joined_player_contacts_public');

        function syncToggles() {
            const enabled = !!playersToggle.checked;
            contactToggle.disabled = !enabled;
            if (!enabled) {
                contactToggle.checked = false;
            }
        }

        playersToggle.addEventListener('change', syncToggles);
        syncToggles();

        // Banner Image Upload Preview
        const bannerImageInput = document.getElementById('banner_image');
        const bannerDropZone = document.getElementById('banner-drop-zone');
        const bannerPlaceholder = document.getElementById('banner-placeholder');
        const bannerPreviewContainer = document.getElementById('banner-preview-container');
        const bannerPreviewImg = document.getElementById('banner-preview-img');
        const bannerFilename = document.getElementById('banner-filename');
        const bannerActions = document.getElementById('banner-actions');
        const btnRemoveBanner = document.getElementById('btn-remove-banner');

        if (bannerImageInput && bannerDropZone) {
            bannerImageInput.addEventListener('change', function (e) {
                const file = this.files[0];
                if (file) {
                    // Check file size (2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Ukuran file terlalu besar! Maksimal 2MB.');
                        this.value = '';
                        resetBannerPreview();
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function (e) {
                        bannerPreviewImg.src = e.target.result;
                        bannerFilename.textContent = file.name;
                        
                        bannerPlaceholder.classList.add('hidden');
                        bannerPreviewContainer.classList.remove('hidden');
                        bannerActions.classList.remove('hidden');
                        bannerDropZone.classList.remove('border-dashed');
                        bannerDropZone.classList.add('border-solid', 'border-brand-500');
                    }
                    reader.readAsDataURL(file);
                } else {
                    resetBannerPreview();
                }
            });

            btnRemoveBanner.addEventListener('click', function () {
                bannerImageInput.value = '';
                resetBannerPreview();
            });

            function resetBannerPreview() {
                bannerPreviewImg.src = '';
                bannerFilename.textContent = '';
                
                bannerPlaceholder.classList.remove('hidden');
                bannerPreviewContainer.classList.add('hidden');
                bannerActions.classList.add('hidden');
                bannerDropZone.classList.remove('border-solid', 'border-brand-500');
                bannerDropZone.classList.add('border-dashed');
            }

            // Drag and drop effects
            ['dragenter', 'dragover'].forEach(eventName => {
                bannerDropZone.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                bannerDropZone.addEventListener(eventName, unhighlight, false);
            });

            function highlight(e) {
                bannerDropZone.classList.add('border-brand-500', 'bg-brand-50/10');
            }

            function unhighlight(e) {
                bannerDropZone.classList.remove('border-brand-500', 'bg-brand-50/10');
            }
        }

        // 2. Default & Min Date/Time Settings (Auto-fill with current local time)
        const dateInput = document.getElementById('tanggal');
        const timeInput = document.getElementById('waktu');

        if (dateInput || timeInput) {
            const now = new Date();
            
            // Local date components
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const localDate = `${year}-${month}-${day}`;
            
            // Local time components
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const localTime = `${hours}:${minutes}`;

            if (dateInput) {
                dateInput.setAttribute('min', localDate);
                if (!dateInput.value) {
                    dateInput.value = localDate;
                }
            }
            if (timeInput) {
                if (!timeInput.value) {
                    timeInput.value = localTime;
                }
            }
        }


        const customRolesList = document.getElementById('custom-roles-list');
        const addRoleButton = document.getElementById('add-role-button');
        const customTotalSlots = document.getElementById('custom-total-slots');
        const customTotalCost = document.getElementById('custom-total-cost');

        const payMethodContainer = document.getElementById('container-metode-pembayaran');
        const gratisBadgeContainer = document.getElementById('container-gratis-badge');

        const existingRoles = JSON.parse(document.getElementById('old_roles_json')?.value || '[]');
        const existingFacilities = JSON.parse(document.getElementById('old_facilities_json')?.value || '[]');
        const facilitiesInputs = document.getElementById('facilities-inputs');

        function formatRupiah(value) {
            return 'Rp ' + Math.round(value).toLocaleString('id-ID');
        }

        function syncPaymentMethodVisibility(totalCost) {
            const isFree = totalCost <= 0;
            payMethodContainer.classList.toggle('hidden', isFree);
            gratisBadgeContainer.classList.toggle('hidden', !isFree);
        }

        function createRoleRow(role = {}) {
            const index = customRolesList.children.length;
            const row = document.createElement('div');
            row.className = 'role-row grid grid-cols-1 sm:grid-cols-[1.5fr_1fr_1fr_0.7fr] gap-3 items-end rounded-xl border border-gray-200 bg-white p-4';
            row.innerHTML = `
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Role</label>
                    <input type="text" name="roles[${index}][name]" value="${role.name ?? ''}" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10" placeholder="Contoh: Pemain">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Slot Maksimal</label>
                    <input type="number" name="roles[${index}][slots]" value="${role.slots ?? 0}" min="1" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10" placeholder="0">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Harga per Slot (Rp)</label>
                    <input type="number" name="roles[${index}][price]" value="${role.price ?? 0}" min="0" step="any" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10" placeholder="0">
                </div>
                <div class="flex items-center justify-end">
                    <button type="button" class="remove-role-button inline-flex w-full sm:w-auto items-center justify-center rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100 transition-all">
                        Hapus
                    </button>
                </div>
            `;
            customRolesList.appendChild(row);
            row.querySelector('.remove-role-button')?.addEventListener('click', () => {
                row.remove();
                refreshRoleInputs();
                refreshCustomTotals();
            });
            row.querySelector(`input[name="roles[${index}][slots]"]`)?.addEventListener('input', refreshCustomTotals);
            row.querySelector(`input[name="roles[${index}][price]"]`)?.addEventListener('input', refreshCustomTotals);
            refreshCustomTotals();
        }

        function refreshRoleInputs() {
            [...customRolesList.children].forEach((row, index) => {
                const inputs = row.querySelectorAll('input');
                inputs.forEach((input) => {
                    const name = input.name.replace(/roles\[\d+\]/, `roles[${index}]`);
                    input.name = name;
                });
            });
        }

        function syncFacilityOptionState(option) {
            const checkbox = option.querySelector('.facility-checkbox');
            const noteInput = option.querySelector('.facility-note');
            const checked = checkbox.checked;
            option.classList.toggle('border-brand-500', checked);
            option.classList.toggle('bg-brand-50/40', checked);
            noteInput.classList.toggle('hidden', !checked);
        }

        function refreshFacilityInputs() {
            facilitiesInputs.innerHTML = '';
            let index = 0;
            document.querySelectorAll('.facility-option').forEach((option) => {
                const checkbox = option.querySelector('.facility-checkbox');
                if (!checkbox.checked) return;
                const key = option.dataset.key;
                const note = option.querySelector('.facility-note').value;

                const keyInput = document.createElement('input');
                keyInput.type = 'hidden';
                keyInput.name = `facilities[${index}][key]`;
                keyInput.value = key;
                facilitiesInputs.appendChild(keyInput);

                const noteInput = document.createElement('input');
                noteInput.type = 'hidden';
                noteInput.name = `facilities[${index}][note]`;
                noteInput.value = note;
                facilitiesInputs.appendChild(noteInput);

                index++;
            });
        }

        function initFacilityOptions() {
            document.querySelectorAll('.facility-option').forEach((option) => {
                const checkbox = option.querySelector('.facility-checkbox');
                const noteInput = option.querySelector('.facility-note');

                const existing = existingFacilities.find((f) => f && f.key === option.dataset.key);
                if (existing) {
                    checkbox.checked = true;
                    noteInput.value = existing.note ?? '';
                }
                syncFacilityOptionState(option);

                checkbox.addEventListener('change', () => {
                    syncFacilityOptionState(option);
                    refreshFacilityInputs();
                });
                noteInput.addEventListener('input', refreshFacilityInputs);
            });
            refreshFacilityInputs();
        }

        function refreshCustomTotals() {
            const rows = [...customRolesList.querySelectorAll('.role-row')];
            let totalSlots = 0;
            let totalCost = 0;
            rows.forEach((row) => {
                const slots = parseInt(row.querySelector('input[name^="roles"][name$="[slots]"]')?.value) || 0;
                const price = parseFloat(row.querySelector('input[name^="roles"][name$="[price]"]')?.value) || 0;
                totalSlots += slots;
                totalCost += slots * price;
            });
            customTotalSlots.innerText = totalSlots;
            customTotalCost.innerText = formatRupiah(totalCost);
            syncPaymentMethodVisibility(totalCost);
        }

        function renderExistingRoles() {
            existingRoles.forEach((role) => createRoleRow(role));
            if (!existingRoles.length) {
                createRoleRow({ name: '', slots: 0, price: 0 });
            }
        }

        if (addRoleButton) addRoleButton.addEventListener('click', () => createRoleRow({ name: '', slots: 0, price: 0 }));

        renderExistingRoles();
        initFacilityOptions();
        refreshCustomTotals();
    })();
</script>
@endpush

