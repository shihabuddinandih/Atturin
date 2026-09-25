<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TournamentPenaltyKick extends Model
{
    protected $fillable = [
        'tournament_match_id',
        'team_id',
        'urutan',
        'hasil',
    ];

    public function match()
    {
        return $this->belongsTo(TournamentMatch::class, 'tournament_match_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
