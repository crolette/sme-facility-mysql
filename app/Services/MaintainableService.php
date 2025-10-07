<?php

namespace App\Services;

use App\Enums\MaintenanceFrequency;
use App\Models\Tenants\Maintainable;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Database\Eloquent\Model;

class MaintainableService
{
    public function create(Model $model, array $data)
    {
        $maintainable = $model->maintainable()->create([...$data]);

        if(isset($data['providers']))
        $maintainable->providers()->sync(collect($data['providers'])->pluck('id'));

        if(isset($data['maintenance_manager_id'])) {
            $maintainable->manager()->associate($data['maintenance_manager_id'])->save();
        }
    }

    public function update(Maintainable $maintainable, $request)
    {
        $maintainable->update([...$request->validated()]);

        $maintainable->providers()->sync(collect($request->validated('providers'))->pluck('id'));

        if ($maintainable->manager === null && $request->validated('maintenance_manager_id')) {
            $maintainable->manager()->associate($request->validated('maintenance_manager_id'))->save();
        }

        if ($maintainable->manager && ($maintainable->manager?->id !== $request->validated('maintenance_manager_id'))) {
            app(MaintainableNotificationSchedulingService::class)->removeNotificationsForOldMaintenanceManager($maintainable, $maintainable->manager);
            $maintainable->manager()->associate($request->validated('maintenance_manager_id'))->save();
        }

        if ($maintainable->wasChanged('maintenance_frequency'))
            $maintainable->next_maintenance_date = calculateNextMaintenanceDate($request->validated('maintenance_frequency'), $request->validated('last_maintenance_date') ?? null);



        $maintainable->save();
    }
};
