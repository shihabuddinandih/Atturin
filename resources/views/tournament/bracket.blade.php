@extends('tournament.layout')

@section('content')
<div class="space-y-6">
    <h1 class="text-xl font-bold text-gray-900">Bracket &amp; Jadwal</h1>

    {{-- Bracket --}}
    @foreach($groupedRounds as $groupLabel => $roundsInGroup)
        @php $isKnockout = $roundsInGroup->first()?->tipe === 'knockout'; @endphp
        <div class="pro-card p-5 overflow-x-auto">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">{{ $groupLabel ? "Grup {$groupLabel}" : 'Bagan Pertandingan' }}</h3>

            @if($isKnockout)
                <div class="bracket-tree flex items-start gap-14 pb-2">
                    @foreach($roundsInGroup->sortBy('urutan') as $round)
                        <div class="bracket-round">
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">{{ $round->nama_ronde }}</p>
                            @foreach($round->matches as $match)
                                @php
                                    $isEntryMatch = $match->home_source_match_id === null && $match->away_source_match_id === null;
                                    $homeIsByeSide = $isEntryMatch && (bool) $match->homeSlot?->is_bye;
                                    $awayIsByeSide = $isEntryMatch && (bool) $match->awaySlot?->is_bye;
                                    $isByeMatch = $isEntryMatch && ($homeIsByeSide !== $awayIsByeSide);
                                @endphp
                                <a href="{{ route('tournament.match.show', [$tournament, $match]) }}" class="bracket-match" data-bye="{{ $isByeMatch ? 1 : 0 }}">
                                    <div class="rounded-xl border border-gray-200 overflow-hidden bg-white shadow-sm hover:border-brand-300 transition-colors">
                                        @foreach(['home', 'away'] as $side)
                                            @php
                                                $team = $side === 'home' ? $match->teamHome : $match->teamAway;
                                                $isByeSide = $side === 'home' ? $homeIsByeSide : $awayIsByeSide;
                                            @endphp
                                            <div class="px-3 py-1.5 text-xs {{ $side === 'home' ? 'border-b border-gray-100' : '' }} {{ $match->pemenang_team_id && $team && (int) $match->pemenang_team_id === (int) $team->id ? 'bg-emerald-50 font-bold text-emerald-800' : 'text-gray-700' }}">
                                                @if($isByeSide)
                                                    <span class="text-gray-300 italic truncate block">(bye)</span>
                                                @elseif($team)
                                                    <span class="truncate block">{{ $team->nama_tim }}</span>
                                                @else
                                                    <span class="text-gray-300 italic truncate block">TBD</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex items-start gap-6 min-w-max pb-2">
                    @foreach($roundsInGroup->sortBy('urutan') as $round)
                        <div class="flex flex-col gap-4 min-w-[200px]">
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">{{ $round->nama_ronde }}</p>
                            @foreach($round->matches as $match)
                                <a href="{{ route('tournament.match.show', [$tournament, $match]) }}" class="rounded-xl border border-gray-200 overflow-hidden hover:border-brand-300 transition-colors">
                                    @foreach(['home', 'away'] as $side)
                                        @php
                                            $team = $side === 'home' ? $match->teamHome : $match->teamAway;
                                        @endphp
                                        <div class="px-3 py-2 text-xs {{ $side === 'home' ? 'border-b border-gray-100' : '' }} {{ $match->pemenang_team_id && $team && (int) $match->pemenang_team_id === (int) $team->id ? 'bg-emerald-50 font-bold text-emerald-800' : 'text-gray-700' }}">
                                            @if($team)
                                                {{ $team->nama_tim }}
                                            @else
                                                <span class="text-gray-300 italic">TBD</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </a>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach

    {{-- Schedule & results --}}
    @foreach($scheduleByRound as $roundName => $roundMatches)
        <div class="pro-card overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">{{ $roundName }}</h3>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($roundMatches as $match)
                    <a href="{{ route('tournament.match.show', [$tournament, $match]) }}" class="p-5 flex items-center justify-between gap-4 hover:bg-gray-50/50 transition-colors">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ $match->teamHome->nama_tim ?? 'TBD' }} <span class="text-gray-300">vs</span> {{ $match->teamAway->nama_tim ?? 'TBD' }}</p>
                            @if($match->jadwal_tanggal)
                                <p class="text-xs text-gray-400 mt-0.5">{{ $match->jadwal_tanggal->translatedFormat('d M Y') }} &middot; {{ $match->jadwal_waktu }} @if($match->lokasi) &middot; {{ $match->lokasi }} @endif</p>
                            @endif
                        </div>
                        <div class="text-right">
                            @if($match->status === 'finished')
                                <p class="text-sm font-bold text-gray-800">{{ $match->skor_home }} - {{ $match->skor_away }}</p>
                            @endif
                            <span class="inline-flex px-2 py-0.5 rounded-md text-[9px] font-bold tracking-wide uppercase bg-gray-100 text-gray-500">
                                {{ \App\Enums\MatchStatus::from($match->status)->label() }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/tournament-bracket.js') }}"></script>
<script>
    // Bracket/schedule data isn't as time-sensitive as a live match score,
    // so a periodic full reload is simpler and safe here.
    setInterval(() => window.location.reload(), 25000);
</script>
@endpush
