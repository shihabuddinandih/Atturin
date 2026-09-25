<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tournament_id',
        'nama_tim',
        'logo',
        'kontak_manajer',
        'grup',
        'seed',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function players()
    {
        return $this->hasMany(TeamPlayer::class);
    }

    public function officials()
    {
        return $this->hasMany(TeamOfficial::class);
    }

    public function jerseys()
    {
        return $this->hasMany(TeamJersey::class);
    }

    public function slot()
    {
        return $this->hasOne(TournamentSlot::class);
    }
}
