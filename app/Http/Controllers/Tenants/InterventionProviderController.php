<?php

namespace App\Http\Controllers\Tenants;

use Exception;
use Inertia\Inertia;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;
use App\Models\Tenants\InterventionAction;
use App\Http\Requests\Tenant\InterventionActionRequest;

class InterventionProviderController extends Controller
{
    public function create(Intervention $intervention, Request $request) {

        $intervention->load('interventionable','ticket','actions');
        // dd($request->getQueryString(), $request->normalizeQueryString($request->getQueryString()));
        $types = CategoryType::where('category', 'action')->get();
        // dd($types);

        return Inertia::render('tenants/interventions/ProviderPage', ['intervention' => $intervention, 'email' => $request->email, 'actionTypes' => $types, 'query' => $request->getQueryString()]);
    }

    public function store(Intervention $intervention, InterventionActionRequest $request) 
    {
        try {
            DB::beginTransaction();

            $action = new InterventionAction($request->validated());

            $action->actionType()->associate($request->validated('action_type_id'));
            if (!$request->validated('creator_email')) {
                $action->creator()->associate($request->validated('created_by'));
            }

            $intervention->actions()->save($action);


            DB::commit();

            return ApiResponse::success(null, 'Intervention action created');
        } catch (Exception $e) {
            DB::rollback();
            return ApiResponse::error('Error during Intervention action creation', [$e->getMessage()]);
        }
        
    }
}
