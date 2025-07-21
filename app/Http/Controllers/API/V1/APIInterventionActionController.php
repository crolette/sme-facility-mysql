<?php

namespace App\Http\Controllers\API\V1;

use Exception;
use App\Helpers\ApiResponse;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use Illuminate\Http\Request;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Ticket;
use App\Models\Tenants\Building;
use App\Services\PictureService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Tenants\Intervention;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Http\Requests\Tenant\TicketRequest;
use App\Http\Requests\Tenant\InterventionRequest;
use App\Http\Requests\Tenant\PictureUploadRequest;
use App\Http\Requests\Tenant\InterventionActionRequest;
use App\Models\Tenants\InterventionAction;

class APIInterventionActionController extends Controller
{
    public function index(Intervention $intervention)
    {
        return ApiResponse::success($intervention->load('actions')->actions);
    }

    public function store(InterventionActionRequest $request, Intervention $intervention)
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

        return ApiResponse::error('Error during Intervention action creation');
    }

    public function update(InterventionActionRequest $request, InterventionAction $action)
    {
        try {
            DB::beginTransaction();


            $action->update($request->safe()->except('created_by', 'creator_email', 'updated_by'));
            $action->updater()->associate($request->validated('updated_by'));
            $action->save();


            DB::commit();
            return ApiResponse::success(null, 'Intervention action updated');
        } catch (Exception $e) {
            DB::rollback();
            return ApiResponse::error('Error during Intervention action update', [$e->getMessage()]);
        }

        return ApiResponse::error('Error during Intervention action update');
    }

    public  function destroy(InterventionAction $action)
    {
        $action->delete();

        return ApiResponse::success(null, 'Action deleted');
    }
}
