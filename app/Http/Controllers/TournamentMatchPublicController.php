<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Services\TournamentPublicService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TournamentMatchPublicController extends Controller
{
    public function __construct(
        private TournamentPublicService $publicService
    ) {}

    public function show(Tournament $tournament, TournamentMatch $match): View
    {
        abort_unless((int) $match->tournament_id === (int) $tournament->id, 404);

        $payload = $this->publicService->buildMatchPayload($match);

        return view('tournament.match', compact('tournament', 'match', 'payload'));
    }

    public function poll(Tournament $tournament, TournamentMatch $match): JsonResponse
    {
        abort_unless((int) $match->tournament_id === (int) $tournament->id, 404);

        return response()->json(['match' => $this->publicService->buildMatchPayload($match)]);
    }
}
