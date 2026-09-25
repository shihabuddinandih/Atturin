<?php

namespace App\Enums;

enum PenaltyKickResult: string
{
    case GOL = 'gol';
    case GAGAL = 'gagal';

    public function label(): string
    {
        return match ($this) {
            self::GOL => 'Gol',
            self::GAGAL => 'Gagal',
        };
    }
}
