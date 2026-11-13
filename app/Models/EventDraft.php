<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EventDraft extends Model
{
    protected $fillable = [
        'admin_id',
        'nama_event',
        'payload',
        'banner_image',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $draft): void {
            if ($draft->banner_image) {
                Storage::disk('public')->delete($draft->banner_image);
            }
        });
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function scopeOwnedBy($query, int $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    /**
     * Data siap pakai untuk prefill form wizard.
     */
    public function toFormState(): array
    {
        $payload = $this->payload ?? [];

        return [
            'id' => $this->id,
            'nama_event' => $this->nama_event,
            'tanggal' => $payload['tanggal'] ?? null,
            'waktu' => $payload['waktu'] ?? null,
            'tempat' => $payload['tempat'] ?? null,
            'roles' => $payload['roles'] ?? [],
            'facilities' => $payload['facilities'] ?? [],
            'show_joined_players_public' => $payload['show_joined_players_public'] ?? true,
            'show_joined_player_contacts_public' => $payload['show_joined_player_contacts_public'] ?? false,
            'payment_mode' => $payload['payment_mode'] ?? 'pay_later',
            'enable_waiting_list' => (bool) ($payload['enable_waiting_list'] ?? false),
            'step' => $payload['step'] ?? 1,
            'banner_url' => $this->banner_image ? asset('storage/' . $this->banner_image) : null,
            'saved_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
