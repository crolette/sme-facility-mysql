<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Tenants\Document;
use App\Models\Tenants\Provider;
use App\Models\Tenants\User;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MaintainableService
{
    public function createMaintainable(Model $model, $request): Model
    {
        $model->maintainable()->updateOrCreate(['maintainable_type' => get_class($model), 'maintainable_id' => $model->id], [...$request->validated()]);

        if ($request->validated('providers')) {
            Debugbar::info(collect($request->validated('providers'))->pluck('id'));
            $model->maintainable->providers()->sync(collect($request->validated('providers'))->pluck('id'));
        }
        if ($request->validated('maintenance_manager_id')) {
            if ($model->maintainable->manager?->id !== $request->validated('maintenance_manager_id')) {
                $model->maintainable->manager()->disassociate()->save();
            }
            $model->maintainable->manager()->associate($request->validated('maintenance_manager_id'))->save();
        }

        return $model;
    }
};
