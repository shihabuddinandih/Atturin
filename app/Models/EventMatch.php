<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventMatch extends Model
{
    protected $fillable = [
        'event_id',
        'urutan',
        'putaran',
        'home_team_id',
        'away_team_id',
        'perkiraan_mulai',
    ];

    public function homeTeam()
    {
        return $this->belongsTo(EventTeam::class, 'home_team_id');
    }

    public function awayTeam()
    {
        return $this->belongsTo(EventTeam::class, 'away_team_id');
    }
}
