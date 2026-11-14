<?php

namespace App\Support;

/**
 * Biaya admin pembayaran online (Midtrans QRIS), ditambahkan ke harga posisi.
 *  - harga Rp 0 (gratis)  → tanpa biaya admin
 *  - ≤ Rp 49.000         → + Rp 1.500
 *  - ≤ Rp 99.000         → + Rp 3.000
 *  - selebihnya          → + 3%
 */
class PaymentFee
{
    public const SETTING_KEY = 'admin_fee_enabled';

    /** Saklar global (diatur superadmin). Default mati: peserta bebas biaya admin. */
    public static function enabled(): bool
    {
        return \App\Models\AppSetting::bool(self::SETTING_KEY, false);
    }

    public static function adminFee(float $base, ?string $method = 'online_banking'): float
    {
        if ($method !== 'online_banking' || $base <= 0) {
            return 0.0;
        }
        if ($base <= 49000) {
            return 1500.0;
        }
        if ($base <= 99000) {
            return 3000.0;
        }

        return (float) round($base * 0.03);
    }

    public static function total(float $base, ?string $method = 'online_banking'): float
    {
        return $base + self::adminFee($base, $method);
    }
}
