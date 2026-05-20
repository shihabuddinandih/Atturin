<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case ONLINE_BANKING = 'online_banking';
    case TUNAI = 'tunai';

    public function label(): string
    {
        return match ($this) {
            self::ONLINE_BANKING => 'Online Banking',
            self::TUNAI => 'Tunai',
        };
    }
}
