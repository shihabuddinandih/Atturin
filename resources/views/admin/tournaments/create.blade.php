@extends('layouts.admin')

@section('content')
<div class="w-full max-w-3xl mx-auto">
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-2">
            <a href="{{ route('admin.tournaments.index') }}" class="hover:text-brand-500 transition-colors">Turnamen</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-600">Buat Turnamen Baru</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Buat Turnamen Baru</h1>
        <p class="text-sm text-gray-500 mt-1">Bagan pertandingan akan otomatis dibuat begitu turnamen ini disimpan.</p>
    </div>

    <div class="pro-card overflow-hidden">
        <form action="{{ route('admin.tournaments.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="p-6 border-b border-gray-100 space-y-4">
                <div>
                    <label for="nama_turnamen" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Turnamen</label>
                    <input type="text" name="nama_turnamen" id="nama_turnamen" value="{{ old('nama_turnamen') }}"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all"
                           required placeholder="Cth: Turnamen Futsal Antar RT 2026">
                    @error('nama_turnamen')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="format" class="block text-sm font-medium text-gray-700 mb-1.5">Format Kompetisi</label>
                    <select name="format" id="format" required
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all">
                        @foreach(\App\Enums\TournamentFormat::cases() as $format)
                            <option value="{{ $format->value }}" {{ old('format') === $format->value ? 'selected' : '' }}>{{ $format->label() }}</option>
                        @endforeach
                    </select>
                    @error('format')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div id="jumlah-grup-field" class="hidden">
                    <label for="jumlah_grup" class="block text-sm font-medium text-gray-700 mb-1.5">Jumlah Grup</label>
                    <input type="number" name="jumlah_grup" id="jumlah_grup" min="2" value="{{ old('jumlah_grup') }}"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all"
                           placeholder="Cth: 4">
                    @error('jumlah_grup')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="jumlah_tim" class="block text-sm font-medium text-gray-700 mb-1.5">Jumlah Tim</label>
                        <input type="number" name="jumlah_tim" id="jumlah_tim" min="2" value="{{ old('jumlah_tim') }}"
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all"
                               required placeholder="8">
                        @error('jumlah_tim')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="jumlah_pemain_per_tim" class="block text-sm font-medium text-gray-700 mb-1.5">Pemain / Tim</label>
                        <input type="number" name="jumlah_pemain_per_tim" id="jumlah_pemain_per_tim" min="1" value="{{ old('jumlah_pemain_per_tim') }}"
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all"
                               required placeholder="5">
                        @error('jumlah_pemain_per_tim')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="jumlah_official_per_tim" class="block text-sm font-medium text-gray-700 mb-1.5">Official / Tim</label>
                        <input type="number" name="jumlah_official_per_tim" id="jumlah_official_per_tim" min="0" value="{{ old('jumlah_official_per_tim') }}"
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all"
                               required placeholder="2">
                        @error('jumlah_official_per_tim')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="lokasi" class="block text-sm font-medium text-gray-700 mb-1.5">Venue</label>
                    <input type="text" name="lokasi" id="lokasi" value="{{ old('lokasi') }}"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all"
                           required placeholder="Cth: GOR Merdeka">
                    @error('lokasi')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" id="tanggal_mulai" value="{{ old('tanggal_mulai') }}"
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all"
                               required>
                        @error('tanggal_mulai')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" id="tanggal_selesai" value="{{ old('tanggal_selesai') }}"
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all"
                               required>
                        @error('tanggal_selesai')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="banner_image" class="block text-sm font-medium text-gray-700 mb-1.5">Banner (opsional)</label>
                    <input type="file" name="banner_image" id="banner_image" accept="image/jpeg,image/png,image/webp"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all">
                    @error('banner_image')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="p-6 bg-gray-50/50 flex items-center justify-end gap-3">
                <a href="{{ route('admin.tournaments.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-800 transition-colors">Batal</a>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-lime-400 hover:bg-lime-500 text-brand-900 font-bold text-sm shadow-lg shadow-lime-400/20 transition-all hover:shadow-xl hover:shadow-lime-400/30">
                    Simpan Turnamen
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const formatSelect = document.getElementById('format');
        const jumlahGrupField = document.getElementById('jumlah-grup-field');

        function syncJumlahGrupVisibility() {
            const needsGroups = formatSelect.value === 'round_robin' || formatSelect.value === 'group_knockout';
            jumlahGrupField.classList.toggle('hidden', !needsGroups);
        }

        formatSelect.addEventListener('change', syncJumlahGrupVisibility);
        syncJumlahGrupVisibility();
    })();
</script>
@endpush
