<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminWalletWithdrawal extends Model
{
    use HasFactory;

    protected $table = 'admin_wallet_withdrawals';

    protected $fillable = [
        'admin_id',
        'amount',
        'payment_method',
        'payment_account',
        'status',
        'note',
        'requested_at',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
