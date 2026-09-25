<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TournamentMatchStat extends Model
{
    protected $fillable = [
        'tournament_match_id',
        'possession_home',
        'possession_away',
        'tembakan_home',
        'tembakan_away',
        'tembakan_tepat_home',
        'tembakan_tepat_away',
        'pojok_home',
        'pojok_away',
        'pelanggaran_home',
        'pelanggaran_away',
    ];

    public function match()
    {
        return $this->belongsTo(TournamentMatch::class, 'tournament_match_id');
    }
}
