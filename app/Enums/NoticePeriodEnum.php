<?php

namespace App\Enums;

use Carbon\Carbon;

enum NoticePeriodEnum: string
{
    case DEFAULT = 'default';
    case FOURTEEN_DAYS = '14_days';
    case ONE_MONTH = '1_month';
    case THREE_MONTHS = '3_months';
    case SIX_MONTHS = '6_months';

    public function label(): string
    {
        return match ($this) {
            self::DEFAULT => 'default',
            self::FOURTEEN_DAYS => '14 days',
            self::ONE_MONTH => '1 month',
            self::THREE_MONTHS => '3 months',
            self::SIX_MONTHS => '6 months',
        };
    }

    /**
     * Ajoute le délai de préavis à une date donnée.
     */
    public function addTo(Carbon $date): Carbon
    {
        return match ($this) {
            self::DEFAULT => $date->copy()->addDays(7),
            self::FOURTEEN_DAYS => $date->copy()->addDays(14),
            self::ONE_MONTH => $date->copy()->addMonth(),
            self::THREE_MONTHS => $date->copy()->addMonths(3),
            self::SIX_MONTHS => $date->copy()->addMonths(6),
        };
    }

    /**
     * Soustrait le délai de préavis à une date donnée.
     */
    public function subFrom(Carbon $date): Carbon
    {
        return match ($this) {
            self::DEFAULT => $date->copy()->subDays(7),
            self::FOURTEEN_DAYS => $date->copy()->subDays(14),
            self::ONE_MONTH => $date->copy()->subMonth(),
            self::THREE_MONTHS => $date->copy()->subMonths(3),
            self::SIX_MONTHS => $date->copy()->subMonths(6),
        };
    }
}
