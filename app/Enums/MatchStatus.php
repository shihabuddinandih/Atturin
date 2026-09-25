<?php

namespace App\Enums;

enum MatchStatus: string
{
    case SCHEDULED = 'scheduled';
    case LIVE = 'live';
    case FINISHED = 'finished';

    public function label(): string
    {
        return match ($this) {
            self::SCHEDULED => 'Terjadwal',
            self::LIVE => 'Berlangsung',
            self::FINISHED => 'Selesai',
        };
    }
}
