<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TournamentPenaltyShootout extends Model
{
    protected $fillable = [
        'tournament_match_id',
        'skor_penalti_home',
        'skor_penalti_away',
    ];

    public function match()
    {
        return $this->belongsTo(TournamentMatch::class, 'tournament_match_id');
    }
}
