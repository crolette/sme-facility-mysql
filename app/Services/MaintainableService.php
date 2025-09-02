<?php

namespace App\Services;

use App\Enums\MaintenanceFrequency;
use App\Models\Tenants\Maintainable;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MaintainableService
{
    public function create(Model $model, $request)
    {
        $maintainable = $model->maintainable()->create([...$request->validated()]);

        $maintainable->providers()->sync(collect($request->validated('providers'))->pluck('id'));

        if ($request->validated('maintenance_manager_id')) {
            $maintainable->manager()->associate($request->validated('maintenance_manager_id'))->save();
        }
    }

    public function update(Maintainable $maintainable, $request)
    {
        $maintainable->update([...$request->validated()]);

        $maintainable->providers()->sync(collect($request->validated('providers'))->pluck('id'));

        if ($maintainable->manager && ($maintainable->manager?->id !== $request->validated('maintenance_manager_id'))) {
            // dump('--- REMOVE maintainable Maintenance Manager ---');
            app(MaintainableNotificationSchedulingService::class)->removeNotificationsForOldMaintenanceManager($maintainable, $maintainable->manager);
            $maintainable->manager()->disassociate()->save();
        }

        if ($maintainable->wasChanged('maintenance_frequency'))
            $maintainable->next_maintenance_date = calculateNextMaintenanceDate($request->validated('maintenance_frequency'), $request->validated('last_maintenance_date') ?? null);

        if ($maintainable->manager === null && $request->validated('maintenance_manager_id')) {
            // dump('--- CREATE maintainable Maintenance Manager NULL ---');
            $maintainable->manager()->associate($request->validated('maintenance_manager_id'))->save();
        }

        $maintainable->save();
    }
};
