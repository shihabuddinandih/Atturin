<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TournamentSlot extends Model
{
    protected $fillable = [
        'tournament_id',
        'grup',
        'kode',
        'urutan',
        'team_id',
        'is_bye',
    ];

    protected $casts = [
        'is_bye' => 'boolean',
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function homeMatches()
    {
        return $this->hasMany(TournamentMatch::class, 'home_slot_id');
    }

    public function awayMatches()
    {
        return $this->hasMany(TournamentMatch::class, 'away_slot_id');
    }
}
