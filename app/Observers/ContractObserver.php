<?php

namespace App\Observers;

use App\Models\Tenants\Contract;
use App\Services\ContractNotificationSchedulingService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class ContractObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Contract $contract)
    {
        app(ContractNotificationSchedulingService::class)->scheduleForContract($contract);
    }

    public function updated(Contract $contract)
    {
        app(ContractNotificationSchedulingService::class)->updateScheduleForContract($contract);
    }
}
