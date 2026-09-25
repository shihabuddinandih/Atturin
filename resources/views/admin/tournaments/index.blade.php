@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Turnamen</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola turnamen, tim, dan pertandingan.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.tournaments.create') }}" class="px-4 py-2 rounded-xl bg-brand-500 text-white text-sm font-semibold hover:bg-brand-600 shadow-lg shadow-brand-500/20">Buat Turnamen Baru</a>
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-1.5 p-1 bg-gray-100 rounded-xl">
            @foreach(['all' => 'Semua', 'draft' => 'Draft', 'berlangsung' => 'Berlangsung', 'selesai' => 'Selesai'] as $key => $label)
                <a href="{{ route('admin.tournaments.index', ['status' => $key]) }}"
                   class="px-4 py-2 rounded-lg text-xs font-semibold transition-all {{ $status === $key ? 'bg-white text-brand-600 shadow-sm' : 'text-gray-500 hover:text-gray-800' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="pro-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/20">
                        <th class="px-6 py-4 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Nama Turnamen</th>
                        <th class="px-6 py-4 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Format</th>
                        <th class="px-6 py-4 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Jadwal & Lokasi</th>
                        <th class="px-6 py-4 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Tim</th>
                        <th class="px-6 py-4 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($tournaments as $tournament)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-semibold text-gray-800">{{ $tournament->nama_turnamen }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-0.5 rounded-md text-[9px] font-bold tracking-wide uppercase bg-indigo-50 text-indigo-700">
                                    {{ \App\Enums\TournamentFormat::from($tournament->format)->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm text-gray-800">{{ $tournament->tanggal_mulai->translatedFormat('d M Y') }} &ndash; {{ $tournament->tanggal_selesai->translatedFormat('d M Y') }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $tournament->lokasi }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm text-gray-700">{{ $tournament->teams_count }} / {{ $tournament->jumlah_tim }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php $tStatus = \App\Enums\TournamentStatus::from($tournament->status); @endphp
                                <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold bg-{{ $tStatus->color() }}-100 text-{{ $tStatus->color() }}-800 uppercase tracking-wider">
                                    {{ $tStatus->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-xs font-semibold">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.tournaments.show', $tournament) }}"
                                       class="p-2 rounded-lg bg-gray-50 hover:bg-gray-100 text-gray-600 border border-gray-100 hover:text-gray-800 transition-colors" title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <form action="{{ route('admin.tournaments.destroy', $tournament) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus turnamen ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-100 transition-colors" title="Hapus Turnamen">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-12 h-12 rounded-full bg-gray-50 border border-gray-100 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                                    </div>
                                    <p class="text-sm text-gray-400">Belum ada turnamen yang dibuat.</p>
                                    <a href="{{ route('admin.tournaments.create') }}" class="text-xs text-brand-500 font-semibold hover:text-brand-600 bg-brand-50 px-3 py-2 rounded-lg border border-brand-100">Buat Turnamen Baru</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($tournaments->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $tournaments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
