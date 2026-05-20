<?php

namespace App\Enums;

enum JoinStatus: string
{
    case JOINED = 'joined';
    case BATAL = 'batal';

    public function label(): string
    {
        return match ($this) {
            self::JOINED => 'Joined',
            self::BATAL => 'Batal',
        };
    }
}
