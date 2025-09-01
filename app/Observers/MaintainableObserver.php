<?php

namespace App\Observers;

use App\Models\Tenants\Asset;
use App\Models\Tenants\Contract;
use Illuminate\Support\Facades\Log;
use App\Models\Tenants\Maintainable;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Services\NotificationSchedulingService;
use App\Services\AssetNotificationSchedulingService;
use App\Services\MaintainableNotificationSchedulingService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class MaintainableObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Maintainable $maintainable)
    {
        app(MaintainableNotificationSchedulingService::class)->createScheduleForMaintainable($maintainable);
    }

    public function updated(Maintainable $maintainable)
    {
        app(MaintainableNotificationSchedulingService::class)->updateScheduleOfMaintainable($maintainable);
    }
}
