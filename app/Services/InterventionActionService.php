<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Tenants\Contract;
use App\Models\Tenants\Document;
use App\Models\Tenants\Intervention;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\Tenants\InterventionAction;

class InterventionActionService
{
    public function create(Intervention $intervention, array $request): void
    {
        $action = new InterventionAction($request);

        $action->actionType()->associate($request['action_type_id']);
        if (!$request['creator_email']) {
            $action->creator()->associate($request['created_by']);
        }

        $intervention->actions()->save($action);
    }

    
};
