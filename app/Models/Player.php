<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Player extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama',
        'kontak',
    ];

    /**
     * Get all events this player is attached to.
     */
    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_player', 'player_id', 'event_id')->withPivot(
            'status_join',
            'hadir',
            'payment_method',
            'payment_amount',
            'payment_status',
            'payment_reference',
            'payment_paid_at'
        )->withTimestamps();
    }
}
