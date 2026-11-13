<?php

namespace App\Enums;

enum JoinStatus: string
{
    case JOINED = 'joined';
    case BATAL = 'batal';
    /** Mode "bayar dulu": slot ditahan sementara selama peserta membayar. Tidak tampil di roster/statistik. */
    case HOLD = 'hold';

    public function label(): string
    {
        return match ($this) {
            self::JOINED => 'Joined',
            self::BATAL => 'Batal',
            self::HOLD => 'Menunggu Bayar',
        };
    }

    /**
     * Status yang boleh diatur manual oleh admin.
     */
    public static function adminSettable(): array
    {
        return [self::JOINED->value, self::BATAL->value];
    }
}
