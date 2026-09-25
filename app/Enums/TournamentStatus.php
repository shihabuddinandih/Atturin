<?php

namespace App\Enums;

enum TournamentStatus: string
{
    case DRAFT = 'draft';
    case BERLANGSUNG = 'berlangsung';
    case SELESAI = 'selesai';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::BERLANGSUNG => 'Berlangsung',
            self::SELESAI => 'Selesai',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::BERLANGSUNG => 'amber',
            self::SELESAI => 'emerald',
        };
    }
}
