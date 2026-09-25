@extends('layouts.admin')

@section('content')
<div class="w-full max-w-4xl mx-auto space-y-6">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-2">
            <a href="{{ route('admin.tournaments.teams.index', $tournament) }}" class="hover:text-brand-500 transition-colors">Tim</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-600">{{ $team->nama_tim }}</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $team->nama_tim }}</h1>
    </div>

    {{-- Team info --}}
    <div class="pro-card overflow-hidden">
        <form action="{{ route('admin.tournaments.teams.update', [$tournament, $team]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <div class="p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-900">Info Tim</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nama_tim" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Tim</label>
                        <input type="text" name="nama_tim" id="nama_tim" value="{{ old('nama_tim', $team->nama_tim) }}"
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all" required>
                        @error('nama_tim')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="kontak_manajer" class="block text-sm font-medium text-gray-700 mb-1.5">Kontak Manajer</label>
                        <input type="text" name="kontak_manajer" id="kontak_manajer" value="{{ old('kontak_manajer', $team->kontak_manajer) }}"
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all">
                        @error('kontak_manajer')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label for="logo" class="block text-sm font-medium text-gray-700 mb-1.5">Logo Tim</label>
                    @if($team->logo)
                        <img src="{{ asset('storage/' . $team->logo) }}" class="w-16 h-16 object-cover rounded-xl mb-2" alt="Logo {{ $team->nama_tim }}">
                    @endif
                    <input type="file" name="logo" id="logo" accept="image/jpeg,image/png,image/webp"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all">
                    @error('logo')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="p-6 bg-gray-50/50 flex items-center justify-end gap-3">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-500 text-white text-sm font-semibold hover:bg-brand-600">Simpan Info Tim</button>
            </div>
        </form>
    </div>

    {{-- Players roster --}}
    <div class="pro-card overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-900">Pemain ({{ $team->players->count() }} / {{ $tournament->jumlah_pemain_per_tim }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/20">
                        <th class="px-6 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">No. Punggung</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Posisi</th>
                        <th class="px-6 py-3 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($team->players as $player)
                        <tr>
                            <td class="px-6 py-3"><input form="player-update-{{ $player->id }}" type="text" name="nama" value="{{ $player->nama }}" class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm"></td>
                            <td class="px-6 py-3"><input form="player-update-{{ $player->id }}" type="number" name="nomor_punggung" value="{{ $player->nomor_punggung }}" class="w-24 rounded-lg border border-gray-200 px-3 py-1.5 text-sm"></td>
                            <td class="px-6 py-3"><input form="player-update-{{ $player->id }}" type="text" name="posisi" value="{{ $player->posisi }}" class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm"></td>
                            <td class="px-6 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button form="player-update-{{ $player->id }}" type="submit" class="px-3 py-1.5 rounded-lg bg-brand-50 text-brand-600 text-xs font-semibold hover:bg-brand-100">Simpan</button>
                                    <button form="player-delete-{{ $player->id }}" type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-600 text-xs font-semibold hover:bg-rose-100">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-8 text-center text-sm text-gray-400">Belum ada pemain.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @foreach($team->players as $player)
            <form id="player-update-{{ $player->id }}" action="{{ route('admin.tournaments.teams.players.update', [$tournament, $team, $player]) }}" method="POST" class="hidden">
                @csrf
                @method('PATCH')
            </form>
            <form id="player-delete-{{ $player->id }}" action="{{ route('admin.tournaments.teams.players.destroy', [$tournament, $team, $player]) }}" method="POST" onsubmit="return confirm('Hapus pemain ini?')" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
        @if($team->players->count() < $tournament->jumlah_pemain_per_tim)
            <form action="{{ route('admin.tournaments.teams.players.store', [$tournament, $team]) }}" method="POST" class="p-6 bg-gray-50/50 grid grid-cols-1 sm:grid-cols-[2fr_1fr_1fr_auto] gap-3 items-end">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nama</label>
                    <input type="text" name="nama" required class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm" placeholder="Nama pemain">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">No. Punggung</label>
                    <input type="number" name="nomor_punggung" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Posisi</label>
                    <input type="text" name="posisi" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm" placeholder="Cth: GK">
                </div>
                <button type="submit" class="px-4 py-2 rounded-xl bg-lime-400 hover:bg-lime-500 text-brand-900 font-bold text-sm">+ Tambah Pemain</button>
            </form>
        @endif
    </div>

    {{-- Jersey colors --}}
    <div class="pro-card overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-900">Jersey ({{ $team->jerseys->count() }})</h3>
            <p class="text-xs text-gray-400 mt-1">Warna jersey yang dimiliki tim ini &mdash; dipilih per pertandingan di Match Center.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/20">
                        <th class="px-6 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Warna</th>
                        <th class="px-6 py-3 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($team->jerseys as $jersey)
                        <tr>
                            <td class="px-6 py-3"><input form="jersey-update-{{ $jersey->id }}" type="text" name="nama" value="{{ $jersey->nama }}" class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm"></td>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <input form="jersey-update-{{ $jersey->id }}" type="color" name="warna" value="{{ $jersey->warna }}" class="w-10 h-8 rounded border border-gray-200 p-0.5">
                                    <span class="w-4 h-4 rounded-full border border-gray-200" style="background-color: {{ $jersey->warna }}"></span>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button form="jersey-update-{{ $jersey->id }}" type="submit" class="px-3 py-1.5 rounded-lg bg-brand-50 text-brand-600 text-xs font-semibold hover:bg-brand-100">Simpan</button>
                                    <button form="jersey-delete-{{ $jersey->id }}" type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-600 text-xs font-semibold hover:bg-rose-100">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-6 py-8 text-center text-sm text-gray-400">Belum ada jersey.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @foreach($team->jerseys as $jersey)
            <form id="jersey-update-{{ $jersey->id }}" action="{{ route('admin.tournaments.teams.jerseys.update', [$tournament, $team, $jersey]) }}" method="POST" class="hidden">
                @csrf
                @method('PATCH')
            </form>
            <form id="jersey-delete-{{ $jersey->id }}" action="{{ route('admin.tournaments.teams.jerseys.destroy', [$tournament, $team, $jersey]) }}" method="POST" onsubmit="return confirm('Hapus jersey ini?')" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
        <form action="{{ route('admin.tournaments.teams.jerseys.store', [$tournament, $team]) }}" method="POST" class="p-6 bg-gray-50/50 grid grid-cols-1 sm:grid-cols-[2fr_auto_auto] gap-3 items-end">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Nama</label>
                <input type="text" name="nama" required class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm" placeholder="Cth: Kandang">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Warna</label>
                <input type="color" name="warna" value="#0052FF" class="w-16 h-10 rounded-xl border border-gray-200 p-0.5">
            </div>
            <button type="submit" class="px-4 py-2 rounded-xl bg-lime-400 hover:bg-lime-500 text-brand-900 font-bold text-sm">+ Tambah Jersey</button>
        </form>
    </div>

    {{-- Officials roster --}}
    <div class="pro-card overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-900">Official ({{ $team->officials->count() }} / {{ $tournament->jumlah_official_per_tim }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/20">
                        <th class="px-6 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Peran</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Kontak</th>
                        <th class="px-6 py-3 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($team->officials as $official)
                        <tr>
                            <td class="px-6 py-3"><input form="official-update-{{ $official->id }}" type="text" name="nama" value="{{ $official->nama }}" class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm"></td>
                            <td class="px-6 py-3"><input form="official-update-{{ $official->id }}" type="text" name="peran" value="{{ $official->peran }}" class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm"></td>
                            <td class="px-6 py-3"><input form="official-update-{{ $official->id }}" type="text" name="kontak" value="{{ $official->kontak }}" class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm"></td>
                            <td class="px-6 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button form="official-update-{{ $official->id }}" type="submit" class="px-3 py-1.5 rounded-lg bg-brand-50 text-brand-600 text-xs font-semibold hover:bg-brand-100">Simpan</button>
                                    <button form="official-delete-{{ $official->id }}" type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-600 text-xs font-semibold hover:bg-rose-100">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-8 text-center text-sm text-gray-400">Belum ada official.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @foreach($team->officials as $official)
            <form id="official-update-{{ $official->id }}" action="{{ route('admin.tournaments.teams.officials.update', [$tournament, $team, $official]) }}" method="POST" class="hidden">
                @csrf
                @method('PATCH')
            </form>
            <form id="official-delete-{{ $official->id }}" action="{{ route('admin.tournaments.teams.officials.destroy', [$tournament, $team, $official]) }}" method="POST" onsubmit="return confirm('Hapus official ini?')" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
        @if($team->officials->count() < $tournament->jumlah_official_per_tim)
            <form action="{{ route('admin.tournaments.teams.officials.store', [$tournament, $team]) }}" method="POST" class="p-6 bg-gray-50/50 grid grid-cols-1 sm:grid-cols-[2fr_1fr_1fr_auto] gap-3 items-end">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nama</label>
                    <input type="text" name="nama" required class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm" placeholder="Nama official">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Peran</label>
                    <input type="text" name="peran" required class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm" placeholder="Cth: Pelatih">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Kontak</label>
                    <input type="text" name="kontak" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm">
                </div>
                <button type="submit" class="px-4 py-2 rounded-xl bg-lime-400 hover:bg-lime-500 text-brand-900 font-bold text-sm">+ Tambah Official</button>
            </form>
        @endif
    </div>
</div>
@endsection
