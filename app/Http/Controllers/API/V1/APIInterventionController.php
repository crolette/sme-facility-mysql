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
use App\Models\Tenants\Provider;
use App\Services\InterventionService;
use Illuminate\Support\Facades\Log;

class APIInterventionController extends Controller
{

    public function __construct(protected PictureService $pictureService, protected InterventionService $interventionService) {}


    public function show(Intervention $intervention)
    {
        $intervention->load('ticket', 'interventionable');
        return ApiResponse::success($intervention, 'Intervention');
    }


    public function store(InterventionRequest $request, PictureUploadRequest $pictureUploadRequest)
    {

        if ($request->user()->cannot('create', [Intervention::class, $request->validated()]))
            return ApiResponse::notAuthorized();

        try {
            DB::beginTransaction();

            $intervention = $this->interventionService->create($request->validated());


            if ($pictureUploadRequest->validated('pictures')) {
                $this->pictureService->uploadAndAttachPictures($intervention, $pictureUploadRequest->validated('pictures'));
            }

            DB::commit();

            return ApiResponse::success(null, 'Intervention created');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            DB::rollback();
            return ApiResponse::error('Error during Intervention creation', ['errors' => $e->getMessage()]);
        }

        return ApiResponse::error('Error during Intervention creation');
    }

    public function update(InterventionRequest $request, Intervention $intervention, PictureUploadRequest $pictureUploadRequest)
    {

        if ($request->user()->cannot('update', $intervention))
            return ApiResponse::notAuthorized();

        try {
            DB::beginTransaction();

            $intervention = $this->interventionService->update($intervention, $request->safe()->except('locationType', 'locationId', 'ticket_id'));

            if ($pictureUploadRequest->validated('pictures')) {
                $this->pictureService->uploadAndAttachPictures($intervention, $pictureUploadRequest->validated('pictures'));
            }

            DB::commit();
            return ApiResponse::success(null, 'Intervention updated');
        } catch (Exception $e) {
            DB::rollback();
            return ApiResponse::error('Error during Intervention update', [$e->getMessage()]);
        }

        return ApiResponse::error('Error during Intervention update');
    }

    public function destroy(Intervention $intervention)
    {
        if (Auth::user()->cannot('delete', $intervention))
            return ApiResponse::notAuthorized();

        $deleted = $this->interventionService->delete($intervention);

        return $deleted ? ApiResponse::success(null, 'Intervention deleted') : ApiResponse::error('Error during intervention deletion');
        // return ApiResponse::success(null, 'Intervention deleted');
    }
}
