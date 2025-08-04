<?php

use Carbon\Carbon;
use App\Enums\MaintenanceFrequency;

if (!function_exists('calculateNextMaintenanceDate')) {

    function calculateNextMaintenanceDate(string $frequency)
    {
        return Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString();
    }
}
