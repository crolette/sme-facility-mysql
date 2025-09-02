<?php

namespace App\Observers;

use App\Models\Tenants\Asset;
use App\Models\Tenants\Contract;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Services\NotificationSchedulingService;
use App\Services\AssetNotificationSchedulingService;
use App\Services\MaintainableNotificationSchedulingService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class AssetObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Asset $asset)
    {
        if ($asset->maintainable)
            app(AssetNotificationSchedulingService::class)->scheduleForAsset($asset);
    }

    public function updated(Asset $asset)
    {
        // dump('--- ASSET OBSERVER UPDATED ---');
        app(AssetNotificationSchedulingService::class)->updateForAsset($asset);
    }

    public function restored(Asset $asset)
    {
        if ($asset->maintainable) {
            app(AssetNotificationSchedulingService::class)->scheduleForAsset($asset);
            app(MaintainableNotificationSchedulingService::class)->createScheduleForMaintainable($asset->maintainable);
        }
    }
}
