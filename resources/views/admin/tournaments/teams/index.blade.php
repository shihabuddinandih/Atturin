@extends('layouts.admin')

@section('content')
<div class="w-full max-w-5xl mx-auto space-y-6">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-2">
            <a href="{{ route('admin.tournaments.index') }}" class="hover:text-brand-500 transition-colors">Turnamen</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('admin.tournaments.show', $tournament) }}" class="hover:text-brand-500 transition-colors">{{ $tournament->nama_turnamen }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-600">Tim</span>
        </div>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Tim &mdash; {{ $tournament->nama_turnamen }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $teams->count() }} / {{ $tournament->jumlah_tim }} tim terdaftar.</p>
            </div>
            @if($teams->count() < $tournament->jumlah_tim)
                <a href="{{ route('admin.tournaments.teams.create', $tournament) }}" class="px-4 py-2 rounded-xl bg-brand-500 text-white text-sm font-semibold hover:bg-brand-600 shadow-lg shadow-brand-500/20">Tambah Tim</a>
            @endif
        </div>
    </div>

    <div class="pro-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/20">
                        <th class="px-6 py-4 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Nama Tim</th>
                        <th class="px-6 py-4 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Kontak Manajer</th>
                        <th class="px-6 py-4 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Pemain</th>
                        <th class="px-6 py-4 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Official</th>
                        <th class="px-6 py-4 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($teams as $team)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-semibold text-gray-800">{{ $team->nama_tim }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $team->kontak_manajer ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $team->players_count }} / {{ $tournament->jumlah_pemain_per_tim }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $team->officials_count }} / {{ $tournament->jumlah_official_per_tim }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-xs font-semibold">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.tournaments.teams.edit', [$tournament, $team]) }}"
                                       class="p-2 rounded-lg bg-gray-50 hover:bg-gray-100 text-gray-600 border border-gray-100 hover:text-gray-800 transition-colors" title="Kelola Roster">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('admin.tournaments.teams.destroy', [$tournament, $team]) }}" method="POST" onsubmit="return confirm('Hapus tim ini beserta seluruh roster-nya?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-100 transition-colors" title="Hapus Tim">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <p class="text-sm text-gray-400">Belum ada tim yang ditambahkan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
