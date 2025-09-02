<?php

use Carbon\Carbon;
use App\Enums\MaintenanceFrequency;

use function PHPUnit\Framework\isInstanceOf;

if (!function_exists('calculateNextMaintenanceDate')) {

    function calculateNextMaintenanceDate(string $frequency, ?string $lastMaintenanceDate = null)
    {
        if (!$lastMaintenanceDate)
            return Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString();

        $nextMaintenanceDate = Carbon::createFromFormat('Y-m-d', $lastMaintenanceDate)->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString();

        if ($nextMaintenanceDate < now())
            return Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString();

        return $nextMaintenanceDate;
    }
}
