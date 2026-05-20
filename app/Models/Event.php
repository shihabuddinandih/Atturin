<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'events';

    protected $fillable = [
        'admin_id',
        'nama_event',
        'tanggal',
        'waktu',
        'tempat',
        'slot_max',
        'metode_pembayaran',
        'iuran_per_pemain',
        'biaya_total_event',
        'skema_iuran',
        'show_joined_players_public',
        'show_joined_player_contacts_public',
        'slug',
        'join_code',
        'required_fields',
    ];

    protected $casts = [
        'iuran_per_pemain' => 'decimal:2',
        'biaya_total_event' => 'decimal:2',
        'show_joined_players_public' => 'boolean',
        'show_joined_player_contacts_public' => 'boolean',
        'required_fields' => 'array',
        'tanggal' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $event): void {
            if (empty($event->join_code)) {
                $event->join_code = strtoupper(Str::random(10));
            }

            if (empty($event->required_fields)) {
                $event->required_fields = ['nama', 'kontak'];
            }

            if (empty($event->slug)) {
                $event->slug = Str::slug($event->nama_event . '-' . uniqid());
            }
        });
    }

    // ─── Relationships ────────────────────────────────────────────

    /**
     * Get the admin who created this event.
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get all players in this event.
     */
    public function players()
    {
        return $this->belongsToMany(Player::class, 'event_player', 'event_id', 'player_id')->withPivot(
            'status_join',
            'hadir',
            'payment_method',
            'payment_amount',
            'payment_status',
            'payment_reference',
            'payment_paid_at'
        )->withTimestamps();
    }

    // ─── Scopes ───────────────────────────────────────────────────

    /**
     * Scope to filter events by admin.
     */
    public function scopeForAdmin($query, int $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    /**
     * Scope to get upcoming events (tanggal >= today).
     */
    public function scopeUpcoming($query)
    {
        return $query->where('tanggal', '>=', now()->toDateString());
    }

    /**
     * Scope to order by latest date/time.
     */
    public function scopeLatestSchedule($query)
    {
        return $query->latest('tanggal')->latest('waktu');
    }

    // ─── Helpers ──────────────────────────────────────────────────

    /**
     * Check if the event slots are full.
     */
    public function isFull(): bool
    {
        $joinedCount = $this->joined_count ?? $this->players()
            ->wherePivot('status_join', 'joined')
            ->count();

        return $joinedCount >= $this->slot_max;
    }
}
