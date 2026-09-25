@extends('layouts.admin')

@section('content')
<div class="w-full max-w-3xl mx-auto">
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-2">
            <a href="{{ route('admin.tournaments.index') }}" class="hover:text-brand-500 transition-colors">Turnamen</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-600">Edit Turnamen</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Turnamen</h1>
    </div>

    <div class="pro-card overflow-hidden">
        <form action="{{ route('admin.tournaments.update', $tournament) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="p-6 border-b border-gray-100 space-y-4">
                <div>
                    <label for="nama_turnamen" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Turnamen</label>
                    <input type="text" name="nama_turnamen" id="nama_turnamen" value="{{ old('nama_turnamen', $tournament->nama_turnamen) }}"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all"
                           required>
                    @error('nama_turnamen')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Format Kompetisi</label>
                        <div class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-2.5 text-sm text-gray-600">{{ \App\Enums\TournamentFormat::from($tournament->format)->label() }}</div>
                        <p class="mt-1.5 text-xs text-gray-400">Bagan sudah dibuat berdasarkan format ini &mdash; tidak bisa diubah.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Jumlah Tim</label>
                        <div class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-2.5 text-sm text-gray-600">{{ $tournament->jumlah_tim }} tim</div>
                        <p class="mt-1.5 text-xs text-gray-400">Bagan sudah dibuat berdasarkan jumlah ini &mdash; tidak bisa diubah.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="jumlah_pemain_per_tim" class="block text-sm font-medium text-gray-700 mb-1.5">Pemain / Tim</label>
                        <input type="number" name="jumlah_pemain_per_tim" id="jumlah_pemain_per_tim" min="1" value="{{ old('jumlah_pemain_per_tim', $tournament->jumlah_pemain_per_tim) }}"
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all"
                               required>
                        @error('jumlah_pemain_per_tim')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="jumlah_official_per_tim" class="block text-sm font-medium text-gray-700 mb-1.5">Official / Tim</label>
                        <input type="number" name="jumlah_official_per_tim" id="jumlah_official_per_tim" min="0" value="{{ old('jumlah_official_per_tim', $tournament->jumlah_official_per_tim) }}"
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all"
                               required>
                        @error('jumlah_official_per_tim')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="lokasi" class="block text-sm font-medium text-gray-700 mb-1.5">Venue</label>
                    <input type="text" name="lokasi" id="lokasi" value="{{ old('lokasi', $tournament->lokasi) }}"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all"
                           required>
                    @error('lokasi')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" id="tanggal_mulai" value="{{ old('tanggal_mulai', $tournament->tanggal_mulai->format('Y-m-d')) }}"
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all"
                               required>
                        @error('tanggal_mulai')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" id="tanggal_selesai" value="{{ old('tanggal_selesai', $tournament->tanggal_selesai->format('Y-m-d')) }}"
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all"
                               required>
                        @error('tanggal_selesai')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="banner_image" class="block text-sm font-medium text-gray-700 mb-1.5">Banner (opsional)</label>
                    @if($tournament->banner_image)
                        <img src="{{ asset('storage/' . $tournament->banner_image) }}" class="w-full max-w-xs rounded-xl mb-2" alt="Banner saat ini">
                    @endif
                    <input type="file" name="banner_image" id="banner_image" accept="image/jpeg,image/png,image/webp"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all">
                    @error('banner_image')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="p-6 bg-gray-50/50 flex items-center justify-end gap-3">
                <a href="{{ route('admin.tournaments.show', $tournament) }}" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-800 transition-colors">Batal</a>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-lime-400 hover:bg-lime-500 text-brand-900 font-bold text-sm shadow-lg shadow-lime-400/20 transition-all hover:shadow-xl hover:shadow-lime-400/30">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
