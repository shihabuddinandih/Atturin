<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TournamentVenue extends Model
{
    protected $fillable = [
        'tournament_id',
        'nama',
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function matches()
    {
        return $this->hasMany(TournamentMatch::class);
    }
}
