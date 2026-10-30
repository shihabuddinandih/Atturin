<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventTeam extends Model
{
    protected $fillable = [
        'event_id',
        'nama_tim',
        'warna',
        'urutan',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function members()
    {
        return $this->hasMany(EventTeamPlayer::class, 'event_team_id');
    }
}
