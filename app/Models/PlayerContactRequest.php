<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerContactRequest extends Model
{
    protected $fillable = [
        'event_id',
        'player_id',
        'requested_by',
        'status',
        'sent_by',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function sentBy()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
