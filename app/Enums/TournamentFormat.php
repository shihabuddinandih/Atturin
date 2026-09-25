<?php

namespace App\Enums;

enum TournamentFormat: string
{
    case SINGLE_ELIMINATION = 'single_elimination';
    case ROUND_ROBIN = 'round_robin';
    case GROUP_KNOCKOUT = 'group_knockout';

    public function label(): string
    {
        return match ($this) {
            self::SINGLE_ELIMINATION => 'Single Elimination',
            self::ROUND_ROBIN => 'Round Robin',
            self::GROUP_KNOCKOUT => 'Grup + Knockout',
        };
    }
}
