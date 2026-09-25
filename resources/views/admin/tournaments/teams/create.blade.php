@extends('layouts.admin')

@section('content')
<div class="w-full max-w-2xl mx-auto">
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-2">
            <a href="{{ route('admin.tournaments.teams.index', $tournament) }}" class="hover:text-brand-500 transition-colors">Tim</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-600">Tambah Tim</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Tambah Tim &mdash; {{ $tournament->nama_turnamen }}</h1>
    </div>

    <div class="pro-card overflow-hidden">
        <form action="{{ route('admin.tournaments.teams.store', $tournament) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="p-6 border-b border-gray-100 space-y-4">
                <div>
                    <label for="nama_tim" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Tim</label>
                    <input type="text" name="nama_tim" id="nama_tim" value="{{ old('nama_tim') }}"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all"
                           required placeholder="Cth: Garuda FC">
                    @error('nama_tim')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="kontak_manajer" class="block text-sm font-medium text-gray-700 mb-1.5">Kontak Manajer (opsional)</label>
                    <input type="text" name="kontak_manajer" id="kontak_manajer" value="{{ old('kontak_manajer') }}"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all"
                           placeholder="Cth: 08123456789">
                    @error('kontak_manajer')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="logo" class="block text-sm font-medium text-gray-700 mb-1.5">Logo Tim (opsional)</label>
                    <input type="file" name="logo" id="logo" accept="image/jpeg,image/png,image/webp"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all">
                    @error('logo')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="p-6 bg-gray-50/50 flex items-center justify-end gap-3">
                <a href="{{ route('admin.tournaments.teams.index', $tournament) }}" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-800 transition-colors">Batal</a>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-lime-400 hover:bg-lime-500 text-brand-900 font-bold text-sm shadow-lg shadow-lime-400/20 transition-all hover:shadow-xl hover:shadow-lime-400/30">
                    Simpan Tim
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
