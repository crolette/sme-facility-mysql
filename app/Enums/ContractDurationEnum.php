<?php

namespace App\Enums;

use Carbon\Carbon;

enum ContractDurationEnum: string
{
    case ONE_MONTH = "1_month";
    case SIX_MONTHS = '6_months';
    case ONE_YEAR = "1_year";
    case TWO_YEARS = "2_years";

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
}
