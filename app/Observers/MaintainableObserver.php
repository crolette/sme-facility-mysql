<?php

namespace App\Observers;

use App\Models\Tenants\Asset;
use App\Models\Tenants\Contract;
use App\Models\Tenants\Maintainable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Services\NotificationSchedulingService;
use App\Services\AssetNotificationSchedulingService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class MaintainableObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Maintainable $maintainable)
    {
        // if ($asset->maintainable)
        // app(AssetNotificationSchedulingService::class)->scheduleForAsset($asset);
    }

    public function updated(Maintainable $maintainable)
    {
        Debugbar::info('MaintainableObserver updated');
        // app(AssetNotificationSchedulingService::class)->updateForAsset($asset);
    }
}
