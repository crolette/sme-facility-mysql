<?php

namespace App\Observers;

use App\Models\Tenants\Contract;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Services\NotificationSchedulingService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class ContractObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Contract $contract)
    {

        app(NotificationSchedulingService::class)->scheduleForContract($contract);
    }

    public function afterCommit(): bool
    {
        return true;
    }
}
