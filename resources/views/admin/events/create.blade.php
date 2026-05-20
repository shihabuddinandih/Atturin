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
        <form action="{{ route('admin.events.store') }}" method="POST">
            @csrf

            {{-- Section: Event Info --}}
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center gap-2 mb-5">
                    <div class="w-8 h-8 rounded-lg bg-brand-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Informasi Event</h3>
                </div>

                <div class="space-y-4">
                    {{-- Quick Sport Template Buttons --}}
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Pilihan Cepat Kategori Olahraga</span>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" onclick="applySportTemplate('Futsal', 15, 'Arena Futsal Center')" class="px-3.5 py-1.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700 bg-white hover:border-brand-500 hover:text-brand-600 hover:bg-brand-50/20 transition-all flex items-center gap-1.5 shadow-sm">
                                ⚽ Futsal (15 Slot)
                            </button>
                            <button type="button" onclick="applySportTemplate('Badminton', 8, 'GOR Bulutangkis')" class="px-3.5 py-1.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700 bg-white hover:border-brand-500 hover:text-brand-600 hover:bg-brand-50/20 transition-all flex items-center gap-1.5 shadow-sm">
                                🏸 Badminton (8 Slot)
                            </button>
                            <button type="button" onclick="applySportTemplate('Mini Soccer', 22, 'Mini Soccer Arena')" class="px-3.5 py-1.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700 bg-white hover:border-brand-500 hover:text-brand-600 hover:bg-brand-50/20 transition-all flex items-center gap-1.5 shadow-sm">
                                🥅 Mini Soccer (22 Slot)
                            </button>
                            <button type="button" onclick="applySportTemplate('Basket', 10, 'Basketball Court')" class="px-3.5 py-1.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700 bg-white hover:border-brand-500 hover:text-brand-600 hover:bg-brand-50/20 transition-all flex items-center gap-1.5 shadow-sm">
                                🏀 Basket (10 Slot)
                            </button>
                        </div>
                    </div>

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

                    <div>
                        <label for="slot_max" class="block text-sm font-medium text-gray-700 mb-1.5">Maksimal Slot Peserta</label>
                        <input type="number" name="slot_max" id="slot_max" min="1" value="{{ old('slot_max') }}"
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all"
                               required placeholder="Cth: 15">
                    </div>
                </div>
            </div>

            {{-- Section: Payment --}}
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center gap-2 mb-5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Pengaturan Pembayaran & Skema Iuran</h3>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="skema_iuran" class="block text-sm font-medium text-gray-700 mb-1.5">Skema Pembagian Iuran</label>
                            <select name="skema_iuran" id="skema_iuran"
                                    class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all" required>
                                <option value="flat" {{ old('skema_iuran', 'flat') === 'flat' ? 'selected' : '' }}>Bagi Rata (Flat Split)</option>
                                <option value="loyalitas" {{ old('skema_iuran') === 'loyalitas' ? 'selected' : '' }}>Subsidi Silang Loyalitas (Loyalty Split)</option>
                            </select>
                        </div>

                        <div>
                            <div id="container-metode-pembayaran">
                                <label for="metode_pembayaran" class="block text-sm font-medium text-gray-700 mb-1.5">Metode Pembayaran</label>
                                <select name="metode_pembayaran" id="metode_pembayaran"
                                        class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all" required>
                                    <option value="tunai" {{ old('metode_pembayaran', 'tunai') === 'tunai' ? 'selected' : '' }}>Tunai</option>
                                    <option value="online_banking" {{ old('metode_pembayaran') === 'online_banking' ? 'selected' : '' }}>Online Banking (Simulasi Midtrans)</option>
                                </select>
                            </div>
                            <div id="container-gratis-badge" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Metode Pembayaran</label>
                                <div class="px-4 py-3 rounded-xl border border-lime-200 bg-lime-50 text-brand-900 font-bold text-xs flex items-center gap-1.5 shadow-sm">
                                    🎁 Gratis (Tanpa Pembayaran)
                                </div>
                            </div>
                        </div>

                        {{-- Input: Total Cost (Used for both schemes) --}}
                        <div id="container-biaya-total">
                            <label for="biaya_total_event" class="block text-sm font-medium text-gray-700 mb-1.5">Total Biaya Event (Rp)</label>
                            <input type="number" name="biaya_total_event" id="biaya_total_event" min="0" step="1000" value="{{ old('biaya_total_event', 0) }}"
                                   class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all"
                                   placeholder="Contoh: 300000" required>
                            <p id="biaya-terbaca" class="text-xs text-brand-600 mt-1.5 font-semibold"></p>
                        </div>
                    </div>

                    {{-- Live dynamic preview container for Flat Split --}}
                    <div id="flat-simulator-box" class="p-4 rounded-xl border border-brand-100 bg-brand-50/20 space-y-3 hidden">
                        <div class="flex items-center gap-1.5 text-xs font-semibold text-brand-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Estimasi Iuran per Peserta (Bagi Rata)
                        </div>
                        <p class="text-xs text-gray-500 leading-relaxed">
                            Biaya total akan dibagi rata sesuai dengan jumlah maksimal slot peserta yang tersedia.
                        </p>
                        <div class="p-3 bg-white border border-gray-100 rounded-lg max-w-xs">
                            <span class="block font-semibold text-gray-700">Iuran Flat / Orang</span>
                            <span class="block font-bold text-brand-600 mt-1 text-lg" id="sim-flat-player">Rp -</span>
                        </div>
                    </div>

                    {{-- Live dynamic preview container for Loyalty Split --}}
                    <div id="loyalty-simulator-box" class="p-4 rounded-xl border border-brand-100 bg-brand-50/20 space-y-3 hidden">
                        <div class="flex items-center gap-1.5 text-xs font-semibold text-brand-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            Kalkulator Estimasi Tarif Dinamis (Subsidi Silang)
                        </div>
                        <p class="text-xs text-gray-500 leading-relaxed">
                            Logika pembagian biaya akan membagi nominal secara otomatis berdasarkan tingkat kehadiran pendaftar dalam event Anda. Peserta yang rajin mendaftar akan mendapat harga lebih murah sebagai penghargaan loyalitas.
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1 text-xs">
                            <div class="p-3 bg-white border border-gray-100 rounded-lg">
                                <span class="block font-semibold text-gray-700">Peserta Baru</span>
                                <span class="block text-[10px] text-gray-400 mt-0.5">Kehadiran: 0 - 2 kali (Bobot 100%)</span>
                                <span class="block font-bold text-gray-900 mt-1" id="sim-new-player">Rp -</span>
                            </div>
                            <div class="p-3 bg-white border border-gray-100 rounded-lg">
                                <span class="block font-semibold text-sky-700">Peserta Cukup Aktif</span>
                                <span class="block text-[10px] text-gray-400 mt-0.5">Kehadiran: 3 - 5 kali (Diskon 15%)</span>
                                <span class="block font-bold text-sky-600 mt-1" id="sim-active-player">Rp -</span>
                            </div>
                            <div class="p-3 bg-white border border-gray-100 rounded-lg">
                                <span class="block font-semibold text-emerald-700">Peserta Sangat Loyal</span>
                                <span class="block text-[10px] text-gray-400 mt-0.5">Kehadiran: >= 6 kali (Diskon 30%)</span>
                                <span class="block font-bold text-emerald-600 mt-1" id="sim-loyal-player">Rp -</span>
                            </div>
                        </div>
                        <div class="text-[10px] text-amber-600 font-medium">
                            * Catatan: Tarif di atas disimulasikan dengan asumsi seluruh kuota penuh dan bobot disebar rata. Saat pendaftaran berlangsung, sistem akan mengkalkulasikan harga final secara instan dan real-time sesuai nomor kontak telepon pendaftar.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section: Privacy --}}
            <div class="p-6 border-b border-gray-100">
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
            <div class="p-6 bg-gray-50/50 flex items-center justify-end gap-3">
                <a href="{{ route('admin.events.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-800 transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-lime-400 hover:bg-lime-500 text-brand-900 font-bold text-sm shadow-lg shadow-lime-400/20 transition-all hover:shadow-xl hover:shadow-lime-400/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    Simpan & Generate Link
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // 1. Quick Sport Template Function
    window.applySportTemplate = function(sport, slots, venue) {
        const nameInput = document.getElementById('nama_event');
        const slotsInput = document.getElementById('slot_max');
        const venueInput = document.getElementById('tempat');

        if (nameInput) nameInput.value = 'Friendly Match ' + sport + ' Weekend';
        if (slotsInput) {
            slotsInput.value = slots;
            // Fire input event to trigger loyalty preview calculation
            slotsInput.dispatchEvent(new Event('input'));
        }
        if (venueInput) venueInput.value = venue;
    };

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

        // 2. Min Date Validation Lock (Lock past dates)
        const dateInput = document.getElementById('tanggal');
        if (dateInput) {
            const today = new Date().toISOString().split('T')[0];
            dateInput.setAttribute('min', today);
        }

        // 3. Skema Iuran dynamic UI toggles
        const skemaSelect = document.getElementById('skema_iuran');
        const simulatorBoxLoyalitas = document.getElementById('loyalty-simulator-box');
        const simulatorBoxFlat = document.getElementById('flat-simulator-box');
        
        const biayaInput = document.getElementById('biaya_total_event');
        const slotsInput = document.getElementById('slot_max');

        const biayaMask = document.getElementById('biaya-terbaca');

        const payMethodContainer = document.getElementById('container-metode-pembayaran');
        const gratisBadgeContainer = document.getElementById('container-gratis-badge');

        function handleSkemaChange() {
            const val = skemaSelect.value;
            if (val === 'flat') {
                if(simulatorBoxFlat) simulatorBoxFlat.classList.remove('hidden');
                if(simulatorBoxLoyalitas) simulatorBoxLoyalitas.classList.add('hidden');
            } else {
                if(simulatorBoxFlat) simulatorBoxFlat.classList.add('hidden');
                if(simulatorBoxLoyalitas) simulatorBoxLoyalitas.classList.remove('hidden');
            }
            runSimulation();
            syncPaymentMethodVisibility();
        }

        // 4. Rupiah Masking and Payment Visibility Handlers
        function syncPaymentMethodVisibility() {
            const totalCost = parseFloat(biayaInput.value) || 0;
            const isFree = (totalCost === 0);
            updateMaskLabel(biayaInput, biayaMask);

            if (isFree) {
                payMethodContainer.classList.add('hidden');
                gratisBadgeContainer.classList.remove('hidden');
            } else {
                payMethodContainer.classList.remove('hidden');
                gratisBadgeContainer.classList.add('hidden');
            }
        }

        function updateMaskLabel(inputEl, labelEl) {
            const price = parseFloat(inputEl.value) || 0;
            if (price > 0) {
                labelEl.innerText = 'Terbaca: Rp ' + Math.round(price).toLocaleString('id-ID');
                labelEl.classList.remove('text-gray-400');
                labelEl.classList.add('text-brand-600');
            } else {
                labelEl.innerText = 'Mencantumkan Rp 0 (Gratis)';
                labelEl.classList.remove('text-brand-600');
                labelEl.classList.add('text-gray-400');
            }
        }

        // 5. Live Dynamic Loyalty Fee Simulator
        function runSimulation() {
            const cost = parseFloat(biayaInput.value) || 0;
            const slots = parseInt(slotsInput.value) || 0;
            
            const formatRupiah = (val) => 'Rp ' + Math.round(val).toLocaleString('id-ID');

            if (cost > 0 && slots > 0) {
                const base = cost / slots;
                
                // Loyalty
                const elNew = document.getElementById('sim-new-player');
                const elActive = document.getElementById('sim-active-player');
                const elLoyal = document.getElementById('sim-loyal-player');
                if(elNew) elNew.innerText = formatRupiah(base);
                if(elActive) elActive.innerText = formatRupiah(base * 0.85);
                if(elLoyal) elLoyal.innerText = formatRupiah(base * 0.70);

                // Flat
                const elFlat = document.getElementById('sim-flat-player');
                if(elFlat) elFlat.innerText = formatRupiah(base);
            } else {
                const elNew = document.getElementById('sim-new-player');
                const elActive = document.getElementById('sim-active-player');
                const elLoyal = document.getElementById('sim-loyal-player');
                if(elNew) elNew.innerText = 'Rp -';
                if(elActive) elActive.innerText = 'Rp -';
                if(elLoyal) elLoyal.innerText = 'Rp -';

                const elFlat = document.getElementById('sim-flat-player');
                if(elFlat) elFlat.innerText = 'Rp -';
            }
        }

        if (skemaSelect) {
            skemaSelect.addEventListener('change', handleSkemaChange);
            handleSkemaChange();
        }

        if (biayaInput) {
            biayaInput.addEventListener('input', () => {
                runSimulation();
                syncPaymentMethodVisibility();
            });
        }
        if (slotsInput) {
            slotsInput.addEventListener('input', runSimulation);
        }
    })();
</script>
@endpush

