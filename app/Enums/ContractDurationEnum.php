<?php

namespace App\Enums;

use Carbon\Carbon;

enum ContractDurationEnum: string
{
    case ONE_MONTH = "1_month";
    case SIX_MONTHS = '6_months';
    case ONE_YEAR = "1_year";
    case TWO_YEARS = "2_years";


    public function label(): string
    {
        return match ($this) {
            self::ONE_MONTH => '1 month',
            self::SIX_MONTHS => '6 months',
            self::ONE_YEAR => '1 year',
            self::TWO_YEARS => '2 years',
        };
    }
    /**
     * Ajoute la durée de contrat à une date donnée.
     */
    public function addTo(Carbon $date): Carbon
    {
        return match ($this) {
            self::ONE_MONTH => $date->copy()->addMonth(),
            self::SIX_MONTHS => $date->copy()->addMonths(6),
            self::ONE_YEAR => $date->copy()->addYear(),
            self::TWO_YEARS => $date->copy()->addYears(2),
        };
    }

    /**
     * Enlève la durée de contrat à une date donnée.
     */
    public function subFrom(Carbon $date): Carbon
    {
        return match ($this) {
            self::ONE_MONTH => $date->copy()->subMonth(),
            self::SIX_MONTHS => $date->copy()->subMonths(6),
            self::ONE_YEAR => $date->copy()->subYear(),
            self::TWO_YEARS => $date->copy()->subYears(2),
        };
    }
}
