<?php

namespace App\Enums;

enum MaintenanceFrequency: string
{
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case BIMONTHLY = "bimonthly";
    case MONTHLY = 'monthly';
    case QUARTERLY = 'quarterly';
    case BIANNUAL = 'biannual';
    case ANNUAL = 'annual';
    case BIENNIAL = 'biennial';
    case ONDEMAND = 'on demand';

    public function days(): int
    {
        return match ($this) {
            self::DAILY => 1,
            self::WEEKLY => 7,
            self::BIMONTHLY => 14,
            self::MONTHLY => 30,
            self::QUARTERLY => 90,
            self::BIANNUAL => 180,
            self::ANNUAL => 365,
            self::BIENNIAL => 730,
        };
    }
}
