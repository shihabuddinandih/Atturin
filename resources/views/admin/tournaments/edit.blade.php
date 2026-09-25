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

    {{-- Venues / lapangan --}}
    <div class="pro-card overflow-hidden mt-6">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-900">Lapangan ({{ $tournament->venues->count() }})</h3>
            <p class="text-xs text-gray-400 mt-1">Tempat bermain tambahan &mdash; dipilih per pertandingan di halaman Jadwal. Kalau belum ada lapangan yang didaftarkan di sini, jadwal tetap memakai kolom venue bebas seperti biasa.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/20">
                        <th class="px-6 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Nama Lapangan</th>
                        <th class="px-6 py-3 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($tournament->venues as $venue)
                        <tr>
                            <td class="px-6 py-3"><input form="venue-update-{{ $venue->id }}" type="text" name="nama" value="{{ $venue->nama }}" class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm"></td>
                            <td class="px-6 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button form="venue-update-{{ $venue->id }}" type="submit" class="px-3 py-1.5 rounded-lg bg-brand-50 text-brand-600 text-xs font-semibold hover:bg-brand-100">Simpan</button>
                                    <button form="venue-delete-{{ $venue->id }}" type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-600 text-xs font-semibold hover:bg-rose-100">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="px-6 py-8 text-center text-sm text-gray-400">Belum ada lapangan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @foreach($tournament->venues as $venue)
            <form id="venue-update-{{ $venue->id }}" action="{{ route('admin.tournaments.venues.update', [$tournament, $venue]) }}" method="POST" class="hidden">
                @csrf
                @method('PATCH')
            </form>
            <form id="venue-delete-{{ $venue->id }}" action="{{ route('admin.tournaments.venues.destroy', [$tournament, $venue]) }}" method="POST" onsubmit="return confirm('Hapus lapangan ini?')" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
        <form action="{{ route('admin.tournaments.venues.store', $tournament) }}" method="POST" class="p-6 bg-gray-50/50 grid grid-cols-1 sm:grid-cols-[2fr_auto] gap-3 items-end">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Lapangan</label>
                <input type="text" name="nama" required class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm" placeholder="Cth: Lapangan A">
            </div>
            <button type="submit" class="px-4 py-2 rounded-xl bg-lime-400 hover:bg-lime-500 text-brand-900 font-bold text-sm">+ Tambah Lapangan</button>
        </form>
    </div>
</div>
@endsection
