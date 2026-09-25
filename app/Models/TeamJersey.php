<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamJersey extends Model
{
    protected $fillable = [
        'team_id',
        'nama',
        'warna',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
