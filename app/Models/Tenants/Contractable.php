<?php

namespace App\Models\Tenants;

use App\Observers\ContractableObserver;
use App\Services\ContractNotificationSchedulingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

class Contractable extends MorphPivot
{
    protected $table = 'contractables';
    public $incrementing = true;

    protected static function booted()
    {
        parent::booted();

        static::created(function ($contractable) {
            // dump('contractable created');
            // dump($contractable->contractable->manager?->id);
            if ($contractable->contractable->manager) {
                app(ContractNotificationSchedulingService::class)->createScheduleForContractNoticeDate($contractable->contract, $contractable->contractable->manager);
                app(ContractNotificationSchedulingService::class)->createScheduleForContractEndDate($contractable->contract, $contractable->contractable->manager);
            }
        });

        static::deleted(function ($contractable) {
            // dump('contractable deleted');
            if ($contractable->contractable->manager)
                app(ContractNotificationSchedulingService::class)->removeNotificationsForMaintenanceManagerWhenContractIsDetached($contractable->contract, $contractable->contractable->manager);
            // dump('*** DELETED in contractable : ' . $contractable->id);
        });
    }




    public function contractable()
    {
        return $this->morphTo();
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
