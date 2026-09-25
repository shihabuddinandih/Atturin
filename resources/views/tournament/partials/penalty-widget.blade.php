{{-- Expects $payload (from TournamentPublicService::buildMatchPayload) --}}
@php
    $homeKicks = collect($payload['penalti_kicks'])->where('sisi', 'home')->values();
    $awayKicks = collect($payload['penalti_kicks'])->where('sisi', 'away')->values();
@endphp
<div id="penalty-widget" class="pro-card p-6 mt-4">
    <h3 class="text-sm font-semibold text-gray-900 mb-3 text-center">Adu Penalti</h3>
    <div class="flex items-center justify-center gap-6 mb-3">
        <span id="penalty-score-home" class="text-2xl font-bold text-gray-900">{{ $homeKicks->where('hasil', 'gol')->count() }}</span>
        <span class="text-[10px] text-gray-400 uppercase tracking-wider">Skor Penalti</span>
        <span id="penalty-score-away" class="text-2xl font-bold text-gray-900">{{ $awayKicks->where('hasil', 'gol')->count() }}</span>
    </div>
    <div class="flex items-start justify-between gap-4">
        <div class="flex-1">
            <p class="text-xs font-semibold text-gray-600 mb-1.5">{{ $payload['team_home'] ?? '-' }}</p>
            <div id="penalty-dots-home" class="flex gap-1.5 flex-wrap">
                @foreach($homeKicks as $kick)
                    <span class="w-3.5 h-3.5 rounded-full {{ $kick['hasil'] === 'gol' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                @endforeach
            </div>
        </div>
        <div class="flex-1 text-right">
            <p class="text-xs font-semibold text-gray-600 mb-1.5">{{ $payload['team_away'] ?? '-' }}</p>
            <div id="penalty-dots-away" class="flex gap-1.5 flex-wrap justify-end">
                @foreach($awayKicks as $kick)
                    <span class="w-3.5 h-3.5 rounded-full {{ $kick['hasil'] === 'gol' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                @endforeach
            </div>
        </div>
    </div>
    <p id="penalty-flash" class="hidden text-center text-lg font-extrabold text-emerald-600 uppercase tracking-widest mt-3">Goal!</p>
</div>
