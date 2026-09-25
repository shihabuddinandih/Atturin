<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TournamentRound extends Model
{
    protected $fillable = [
        'tournament_id',
        'tipe',
        'nama_ronde',
        'urutan',
        'grup',
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function matches()
    {
        return $this->hasMany(TournamentMatch::class)->orderBy('bracket_position');
    }
}
