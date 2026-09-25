<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Tournament extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'admin_id',
        'nama_turnamen',
        'slug',
        'format',
        'jumlah_tim',
        'jumlah_pemain_per_tim',
        'jumlah_official_per_tim',
        'lokasi',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'konfigurasi',
        'banner_image',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'konfigurasi' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $tournament): void {
            if (empty($tournament->slug)) {
                $tournament->slug = Str::slug($tournament->nama_turnamen . '-' . uniqid());
            }

            if (empty($tournament->status)) {
                $tournament->status = 'draft';
            }
        });
    }

    // ─── Relationships ────────────────────────────────────────────

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public function rounds()
    {
        return $this->hasMany(TournamentRound::class)->orderBy('urutan');
    }

    public function slots()
    {
        return $this->hasMany(TournamentSlot::class)->orderBy('urutan');
    }

    public function matches()
    {
        return $this->hasMany(TournamentMatch::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeForAdmin($query, int $adminId)
    {
        return $query->where('admin_id', $adminId);
    }
}
