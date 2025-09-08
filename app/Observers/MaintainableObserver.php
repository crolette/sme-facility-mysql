<?php

namespace App\Observers;

use App\Models\Tenants\Maintainable;
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
        // dump('--- MAINTAINABLE OBERSERVER UPDATED ---');
        app(MaintainableNotificationSchedulingService::class)->updateScheduleOfMaintainable($maintainable);
    }
}
