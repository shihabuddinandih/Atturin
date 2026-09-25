@extends('layouts.admin')

@section('content')
<div class="w-full max-w-5xl mx-auto space-y-6">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-2">
            <a href="{{ route('admin.tournaments.show', $tournament) }}" class="hover:text-brand-500 transition-colors">{{ $tournament->nama_turnamen }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-600">Jadwal</span>
        </div>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Jadwal Pertandingan</h1>
                <p class="text-sm text-gray-500 mt-1">Atur tanggal, waktu, dan venue untuk pertandingan yang timnya sudah lengkap.</p>
            </div>
            <a href="{{ route('admin.tournaments.bracket.show', $tournament) }}" class="px-4 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:border-brand-200 hover:text-brand-600 transition-colors">Lihat Bracket</a>
        </div>
    </div>

    @foreach($matches as $roundName => $roundMatches)
        <div class="pro-card overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">{{ $roundName }}</h3>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($roundMatches as $match)
                    <div class="p-5 flex flex-col lg:flex-row lg:items-center gap-4">
                        <div class="flex-1">
                            @if($match->isReadyToSchedule())
                                <p class="text-sm font-semibold text-gray-800">{{ $match->teamHome->nama_tim }} <span class="text-gray-300">vs</span> {{ $match->teamAway->nama_tim }}</p>
                            @else
                                <p class="text-sm text-gray-400 italic">{{ $match->teamHome->nama_tim ?? 'Menunggu tim' }} <span class="text-gray-300">vs</span> {{ $match->teamAway->nama_tim ?? 'Menunggu tim' }}</p>
                            @endif
                            <span class="inline-flex mt-1 px-2 py-0.5 rounded-md text-[9px] font-bold tracking-wide uppercase bg-gray-100 text-gray-500">
                                {{ \App\Enums\MatchStatus::from($match->status)->label() }}
                            </span>
                        </div>

                        @if($match->isReadyToSchedule())
                            <form action="{{ route('admin.tournaments.schedule.update', [$tournament, $match]) }}" method="POST" class="grid grid-cols-2 lg:grid-cols-[auto_auto_1fr_auto] gap-2 items-end">
                                @csrf
                                @method('PATCH')
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-500 mb-1">Tanggal</label>
                                    <input type="date" name="jadwal_tanggal" value="{{ $match->jadwal_tanggal?->format('Y-m-d') }}" required
                                           class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm" {{ $match->status !== 'scheduled' ? 'disabled' : '' }}>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-500 mb-1">Waktu</label>
                                    <input type="time" name="jadwal_waktu" value="{{ $match->jadwal_waktu }}" required
                                           class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm" {{ $match->status !== 'scheduled' ? 'disabled' : '' }}>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-500 mb-1">Venue</label>
                                    <input type="text" name="lokasi" value="{{ $match->lokasi ?? $tournament->lokasi }}" placeholder="{{ $tournament->lokasi }}"
                                           class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm" {{ $match->status !== 'scheduled' ? 'disabled' : '' }}>
                                </div>
                                @if($match->status === 'scheduled')
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-brand-500 text-white text-xs font-semibold hover:bg-brand-600">Simpan</button>
                                @endif
                            </form>
                            <a href="{{ route('admin.tournaments.matches.show', [$tournament, $match]) }}" class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:border-brand-200 hover:text-brand-600 transition-colors whitespace-nowrap">
                                {{ $match->status === 'finished' ? 'Lihat Hasil' : 'Match Center' }}
                            </a>
                        @else
                            <p class="text-xs text-gray-400">Belum bisa dijadwalkan</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
@endsection
