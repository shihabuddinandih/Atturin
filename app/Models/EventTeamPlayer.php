<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventTeamPlayer extends Model
{
    protected $fillable = [
        'event_id',
        'event_team_id',
        'player_id',
        'role_name',
    ];

    public function team()
    {
        return $this->belongsTo(EventTeam::class, 'event_team_id');
    }

    public function player()
    {
        return $this->belongsTo(Player::class)->withTrashed();
    }
}
