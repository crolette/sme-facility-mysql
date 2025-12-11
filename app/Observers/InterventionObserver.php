<?php

namespace App\Observers;

use Carbon\Carbon;
use App\Events\TicketClosed;
use App\Enums\InterventionStatus;
use App\Models\Tenants\Intervention;
use App\Services\InterventionNotificationSchedulingService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class InterventionObserver  implements ShouldHandleEventsAfterCommit
{
    public function created(Intervention $intervention)
    {
        // dump('InterventionObserver created');
        app(InterventionNotificationSchedulingService::class)->scheduleForIntervention($intervention);
    }

    public function updated(Intervention $intervention)
    {
        app(InterventionNotificationSchedulingService::class)->updateScheduleForIntervention($intervention);

        if ($intervention->wasChanged('status') && ($intervention->status === InterventionStatus::COMPLETED || $intervention->status === InterventionStatus::CANCELLED)) {
            if ($intervention->ticket) {
                $intervention->ticket?->closeTicket();
            }
        }
    }
}
