<?php

namespace App\Http\Controllers\Tenants;

use Exception;
use Inertia\Inertia;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Services\PictureService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Models\Tenants\InterventionAction;
use App\Services\InterventionActionService;
use App\Events\InterventionAddedByProviderEvent;
use App\Http\Requests\Tenant\PictureUploadRequest;
use App\Http\Requests\Tenant\InterventionActionRequest;

class InterventionProviderController extends Controller
{
    public function __construct(
        protected InterventionActionService $interventionActionService,
        protected PictureService $pictureService
    ) {}

    public function create(Intervention $intervention, Request $request, ) {
        
        $intervention->select('id', 'intervention_type_id', 'description', 'updated_at')->with('interventionable','ticket', 'actions:id,action_type_id,intervention_id,description')->get();
        
        $asset = $intervention->interventionable;
        $pastInterventions = $asset->interventions()->select('id', 'intervention_type_id', 'description', 'updated_at')->with('actions:id,action_type_id,intervention_id,description,updated_at')->whereNot('id', $intervention->id)->get();

        $types = CategoryType::where('category', 'action')->get();

        return Inertia::render('tenants/interventions/ProviderPage', ['intervention' => $intervention, 'email' => $request->email, 'actionTypes' => $types, 'query' => $request->getQueryString(), 'pastInterventions' => $pastInterventions]);
    }

    public function store(Intervention $intervention, InterventionActionRequest $request, PictureUploadRequest $pictureUploadRequest) 
    {
        try {
            DB::beginTransaction();

            $interventionAction = $this->interventionActionService->create($intervention, $request->validated());

            if ($pictureUploadRequest->validated('pictures')) {
                $this->pictureService->uploadAndAttachPictures($interventionAction, $pictureUploadRequest->validated('pictures'));
            }

            event(new InterventionAddedByProviderEvent($intervention, $interventionAction));

            DB::commit();

            return ApiResponse::success(null, 'Intervention action created');
        } catch (Exception $e) {
            DB::rollback();
            return ApiResponse::error('Error during Intervention action creation', [$e->getMessage()]);
        }
        
    }
}
