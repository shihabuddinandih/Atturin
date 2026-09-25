<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Services\TournamentPublicService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TournamentLivescoreController extends Controller
{
    public function __construct(
        private TournamentPublicService $publicService
    ) {}

    public function show(Tournament $tournament): View
    {
        $matches = $this->publicService->scheduledMatches($tournament);

        return view('tournament.show', compact('tournament', 'matches'));
    }

    public function poll(Tournament $tournament): JsonResponse
    {
        return response()->json(['matches' => $this->publicService->buildScheduledListPayload($tournament)]);
    }
}
