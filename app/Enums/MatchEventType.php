<?php

namespace App\Enums;

enum MatchEventType: string
{
    case GOAL = 'goal';
    case KARTU_KUNING = 'kartu_kuning';
    case KARTU_MERAH = 'kartu_merah';
    case PERGANTIAN_PEMAIN = 'pergantian_pemain';
    case LAINNYA = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::GOAL => 'Gol',
            self::KARTU_KUNING => 'Kartu Kuning',
            self::KARTU_MERAH => 'Kartu Merah',
            self::PERGANTIAN_PEMAIN => 'Pergantian Pemain',
            self::LAINNYA => 'Lainnya',
        };
    }
}
