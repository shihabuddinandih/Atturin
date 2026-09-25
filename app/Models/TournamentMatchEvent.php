<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TournamentMatchEvent extends Model
{
    protected $fillable = [
        'tournament_match_id',
        'team_id',
        'team_player_id',
        'tipe',
        'menit',
        'babak',
        'catatan',
        'dicatat_oleh',
    ];

    protected $casts = [
        'babak' => 'integer',
    ];

    public function match()
    {
        return $this->belongsTo(TournamentMatch::class, 'tournament_match_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function player()
    {
        return $this->belongsTo(TeamPlayer::class, 'team_player_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}
