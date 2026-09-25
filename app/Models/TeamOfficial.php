<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeamOfficial extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'nama',
        'peran',
        'kontak',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
