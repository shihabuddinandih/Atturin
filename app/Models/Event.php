<?php

namespace App\Models;

use App\Enums\JoinStatus;
use App\Enums\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    public const PAYMENT_MODE_PAY_LATER = 'pay_later';
    public const PAYMENT_MODE_PAY_FIRST = 'pay_first';

    /** Lama slot ditahan (sekaligus masa berlaku QRIS) untuk mode bayar dulu. */
    public const HOLD_MINUTES = 3;

    /**
     * Setelah waktu tahan habis, slot masih dicadangkan sebentar agar pembayaran QRIS di detik
     * terakhir (QR dibulatkan per menit + jeda settlement) tidak bentrok dengan pendaftar baru.
     */
    public const HOLD_GRACE_SECONDS = 120;

    /**
     * Batas waktu sebelum slot hold benar-benar dilepas.
     */
    public static function holdReleaseAt($expiresAt): ?\Carbon\Carbon
    {
        return $expiresAt ? \Carbon\Carbon::parse($expiresAt)->addSeconds(self::HOLD_GRACE_SECONDS) : null;
    }

    protected $table = 'events';

    protected $fillable = [
        'admin_id',
        'nama_event',
        'tanggal',
        'waktu',
        'tempat',
        'slot_max',
        'metode_pembayaran',
        'payment_mode',
        'iuran_per_pemain',
        'biaya_total_event',
        'facilities',
        'skema_iuran',
        'roles',
        'enable_waiting_list',
        'show_joined_players_public',
        'show_joined_player_contacts_public',
        'slug',
        'join_code',
        'required_fields',
        'banner_image',
        'team_draw_config',
        'team_draw_published_at',
    ];

    protected $casts = [
        'iuran_per_pemain' => 'decimal:2',
        'biaya_total_event' => 'decimal:2',
        'show_joined_players_public' => 'boolean',
        'show_joined_player_contacts_public' => 'boolean',
        'required_fields' => 'array',
        'roles' => 'array',
        'facilities' => 'array',
        'enable_waiting_list' => 'boolean',
        'tanggal' => 'date',
        'team_draw_config' => 'array',
        'team_draw_published_at' => 'datetime',
    ];

    /**
     * Waiting list entries for this event.
     */
    public function waitlists()
    {
        return $this->hasMany(EventWaitlist::class, 'event_id');
    }

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
            'registration_token',
            'confirmation_sent_at',
            'status_join',
            'hadir',
            'payment_method',
            'payment_amount',
            'role_name',
            'payment_status',
            'payment_reference',
            'payment_paid_at',
            'payment_expires_at',
            'payment_snap_token'
        )->withTimestamps();
    }

    /**
     * Teams created by the random team draw.
     */
    public function teams()
    {
        return $this->hasMany(EventTeam::class)->orderBy('urutan');
    }

    /**
     * Team draw assignments (player → team).
     */
    public function teamPlayers()
    {
        return $this->hasMany(EventTeamPlayer::class);
    }

    /**
     * Generated match schedule between the drawn teams.
     */
    public function matches()
    {
        return $this->hasMany(EventMatch::class)->orderBy('urutan');
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

    public function requiresPaymentFirst(): bool
    {
        return $this->payment_mode === self::PAYMENT_MODE_PAY_FIRST;
    }

    /**
     * Jumlah slot terpakai: peserta joined + peserta "hold" yang masa tahannya belum habis.
     * Dipakai untuk cek kapasitas; roster & statistik tetap hanya menghitung joined.
     */
    public function occupiedSlotCount(?string $roleName = null): int
    {
        return $this->occupiedSlotsQuery()
            ->when($roleName !== null, fn ($q) => $q->where('event_player.role_name', $roleName))
            ->count();
    }

    /**
     * @return array<string, int> jumlah slot terpakai per role_name
     */
    public function occupiedSlotCountsByRole(): array
    {
        return $this->occupiedSlotsQuery()
            ->selectRaw("COALESCE(event_player.role_name, 'Tanpa Role') as role_key, COUNT(*) as total")
            ->groupBy('role_key')
            ->pluck('total', 'role_key')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    private function occupiedSlotsQuery()
    {
        return DB::table('event_player')
            ->where('event_player.event_id', $this->id)
            ->where(function ($q) {
                $q->where('event_player.status_join', JoinStatus::JOINED->value)
                    ->orWhere(function ($hold) {
                        $hold->where('event_player.status_join', JoinStatus::HOLD->value)
                            ->where('event_player.payment_expires_at', '>', now()->subSeconds(self::HOLD_GRACE_SECONDS));
                    });
            });
    }
}
