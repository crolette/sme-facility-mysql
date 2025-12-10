<?php

namespace App\Observers;

use Illuminate\Support\Facades\Log;
use App\Models\Tenants\Maintainable;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Services\MaintainableNotificationSchedulingService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class MaintainableObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Maintainable $maintainable)
    {
        // dump('--- MAINTAINABLE OBERSERVER CREATED ---');
        app(MaintainableNotificationSchedulingService::class)->createScheduleForMaintainable($maintainable);
    }

    public function updated(Maintainable $maintainable)
    {
        Debugbar::info('--- MAINTAINABLE OBERSERVER UPDATED ---');
        debugbar::info($maintainable->getChanges());
        app(MaintainableNotificationSchedulingService::class)->updateScheduleOfMaintainable($maintainable);
    }
}
