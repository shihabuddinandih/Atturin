<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeamPlayer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'nama',
        'nomor_punggung',
        'posisi',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
