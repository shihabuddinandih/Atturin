<?php

use App\Http\Controllers\Admin\Tournament\BracketController;
use App\Http\Controllers\Admin\Tournament\MatchController;
use App\Http\Controllers\Admin\Tournament\MatchEventController;
use App\Http\Controllers\Admin\Tournament\MatchPenaltyKickController;
use App\Http\Controllers\Admin\Tournament\MatchStatController;
use App\Http\Controllers\Admin\Tournament\ScheduleController;
use App\Http\Controllers\Admin\Tournament\TeamController;
use App\Http\Controllers\Admin\Tournament\TeamJerseyController;
use App\Http\Controllers\Admin\Tournament\TeamOfficialController;
use App\Http\Controllers\Admin\Tournament\TeamPlayerController;
use App\Http\Controllers\Admin\Tournament\TournamentController;
use App\Http\Controllers\Admin\Tournament\TournamentVenueController;
use App\Http\Controllers\TournamentBracketPublicController;
use App\Http\Controllers\TournamentLivescoreController;
use App\Http\Controllers\TournamentMatchPublicController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/turnamen')->name('admin.tournaments.')->middleware(['auth', 'admin.only'])->group(function () {
    Route::get('/', [TournamentController::class, 'index'])->name('index');
    Route::get('create', [TournamentController::class, 'create'])->name('create');
    Route::post('/', [TournamentController::class, 'store'])->name('store');
    Route::get('{tournament}', [TournamentController::class, 'show'])->name('show');
    Route::get('{tournament}/edit', [TournamentController::class, 'edit'])->name('edit');
    Route::patch('{tournament}', [TournamentController::class, 'update'])->name('update');
    Route::delete('{tournament}', [TournamentController::class, 'destroy'])->name('destroy');

    Route::get('{tournament}/tim', [TeamController::class, 'index'])->name('teams.index');
    Route::get('{tournament}/tim/create', [TeamController::class, 'create'])->name('teams.create');
    Route::post('{tournament}/tim', [TeamController::class, 'store'])->name('teams.store');
    Route::get('{tournament}/tim/{team}/edit', [TeamController::class, 'edit'])->name('teams.edit');
    Route::patch('{tournament}/tim/{team}', [TeamController::class, 'update'])->name('teams.update');
    Route::delete('{tournament}/tim/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');

    Route::post('{tournament}/tim/{team}/pemain', [TeamPlayerController::class, 'store'])->name('teams.players.store');
    Route::patch('{tournament}/tim/{team}/pemain/{player}', [TeamPlayerController::class, 'update'])->name('teams.players.update');
    Route::delete('{tournament}/tim/{team}/pemain/{player}', [TeamPlayerController::class, 'destroy'])->name('teams.players.destroy');

    Route::post('{tournament}/tim/{team}/official', [TeamOfficialController::class, 'store'])->name('teams.officials.store');
    Route::patch('{tournament}/tim/{team}/official/{official}', [TeamOfficialController::class, 'update'])->name('teams.officials.update');
    Route::delete('{tournament}/tim/{team}/official/{official}', [TeamOfficialController::class, 'destroy'])->name('teams.officials.destroy');

    Route::post('{tournament}/tim/{team}/jersey', [TeamJerseyController::class, 'store'])->name('teams.jerseys.store');
    Route::patch('{tournament}/tim/{team}/jersey/{jersey}', [TeamJerseyController::class, 'update'])->name('teams.jerseys.update');
    Route::delete('{tournament}/tim/{team}/jersey/{jersey}', [TeamJerseyController::class, 'destroy'])->name('teams.jerseys.destroy');

    Route::post('{tournament}/lapangan', [TournamentVenueController::class, 'store'])->name('venues.store');
    Route::patch('{tournament}/lapangan/{venue}', [TournamentVenueController::class, 'update'])->name('venues.update');
    Route::delete('{tournament}/lapangan/{venue}', [TournamentVenueController::class, 'destroy'])->name('venues.destroy');

    Route::get('{tournament}/bracket', [BracketController::class, 'show'])->name('bracket.show');
    Route::post('{tournament}/bracket/slot/{slot}', [BracketController::class, 'assignSlot'])->name('bracket.assignSlot');
    Route::delete('{tournament}/bracket/slot/{slot}', [BracketController::class, 'clearSlot'])->name('bracket.clearSlot');
    Route::post('{tournament}/bracket/slot/{slot}/bye', [BracketController::class, 'markBye'])->name('bracket.markBye');
    Route::delete('{tournament}/bracket/slot/{slot}/bye', [BracketController::class, 'unmarkBye'])->name('bracket.unmarkBye');

    Route::get('{tournament}/jadwal', [ScheduleController::class, 'index'])->name('schedule.index');
    Route::patch('{tournament}/jadwal/{tournamentMatch}', [ScheduleController::class, 'update'])->name('schedule.update');

    Route::get('{tournament}/pertandingan/{match}', [MatchController::class, 'show'])->name('matches.show');
    Route::post('{tournament}/pertandingan/{match}/start', [MatchController::class, 'start'])->name('matches.start');
    Route::post('{tournament}/pertandingan/{match}/pause', [MatchController::class, 'pause'])->name('matches.pause');
    Route::post('{tournament}/pertandingan/{match}/resume', [MatchController::class, 'resume'])->name('matches.resume');
    Route::patch('{tournament}/pertandingan/{match}/jersey', [MatchController::class, 'setJersey'])->name('matches.setJersey');
    Route::post('{tournament}/pertandingan/{match}/babak/lanjut', [MatchController::class, 'advanceBabak'])->name('matches.babak.advance');
    Route::post('{tournament}/pertandingan/{match}/finish', [MatchController::class, 'finish'])->name('matches.finish');

    Route::post('{tournament}/pertandingan/{match}/events', [MatchEventController::class, 'store'])->name('matches.events.store');
    Route::patch('{tournament}/pertandingan/{match}/events/{event}', [MatchEventController::class, 'update'])->name('matches.events.update');
    Route::delete('{tournament}/pertandingan/{match}/events/{event}', [MatchEventController::class, 'destroy'])->name('matches.events.destroy');

    Route::patch('{tournament}/pertandingan/{match}/stats', [MatchStatController::class, 'update'])->name('matches.stats.update');

    Route::post('{tournament}/pertandingan/{match}/penalti/mulai', [MatchController::class, 'startPenalty'])->name('matches.penalty.start');
    Route::post('{tournament}/pertandingan/{match}/penalti', [MatchPenaltyKickController::class, 'store'])->name('matches.penalty.kicks.store');
    Route::delete('{tournament}/pertandingan/{match}/penalti/{kick}', [MatchPenaltyKickController::class, 'destroy'])->name('matches.penalty.kicks.destroy');
});

// Public — no auth, reachable at /turnamen/{slug} for now; wrapped in a
// subdomain later (M5) without touching these controllers/views.
Route::name('tournament.')->group(function () {
    Route::get('turnamen/{tournament:slug}', [TournamentLivescoreController::class, 'show'])->name('show');
    Route::get('turnamen/{tournament:slug}/livescore.json', [TournamentLivescoreController::class, 'poll'])
        ->middleware('throttle:60,1')->name('livescore.poll');

    Route::get('turnamen/{tournament:slug}/bracket', [TournamentBracketPublicController::class, 'show'])->name('bracket');

    Route::get('turnamen/{tournament:slug}/pertandingan/{match}', [TournamentMatchPublicController::class, 'show'])->name('match.show');
    Route::get('turnamen/{tournament:slug}/pertandingan/{match}/livescore.json', [TournamentMatchPublicController::class, 'poll'])
        ->middleware('throttle:60,1')->name('match.poll');
});
