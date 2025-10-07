<?php

namespace App\Observers;

use App\Models\Tenants\Asset;
use Illuminate\Support\Facades\Log;
use App\Services\AssetNotificationSchedulingService;
use App\Services\MaintainableNotificationSchedulingService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class AssetObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Asset $asset)
    {
        Log::info('--- ASSET OBSERVER CREATED  : ' . $asset->reference_code ?? 'NOCODE');
        if ($asset->maintainable)
            app(AssetNotificationSchedulingService::class)->scheduleForAsset($asset);
    }

    public function updated(Asset $asset)
    {
        Log::info('--- ASSET OBSERVER UPDATED  : ' . $asset->reference_code);
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
