<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventWaitlist extends Model
{
    use HasFactory;

    protected $table = 'event_waitlists';

    protected $fillable = [
        'event_id',
        'player_id',
        'phone',
        'status',
        'token',
        'contacted_at',
        'expires_at',
        'role_name',
        'payment_amount',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
        'expires_at' => 'datetime',
        'payment_amount' => 'decimal:2',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}
