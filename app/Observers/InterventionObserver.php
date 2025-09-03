<?php

namespace App\Observers;

use App\Models\Tenants\Intervention;
use App\Services\InterventionNotificationSchedulingService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class InterventionObserver  implements ShouldHandleEventsAfterCommit
{
    public function created(Intervention $intervention)
    {
        dump('InterventionObserver created');
        app(InterventionNotificationSchedulingService::class)->scheduleForIntervention($intervention);
    }

    public function updated(Intervention $intervention)
    {
        dump('InterventionObserver updated');
        // app(InterventionNotificationSchedulingService::class)->updateScheduleForIntervention($intervention);
    }
}
