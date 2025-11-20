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
use Illuminate\Support\Facades\Log;

class APIInterventionController extends Controller
{

    public function __construct(protected PictureService $pictureService) {}


    public function show(Intervention $intervention)
    {
        $intervention->load('ticket', 'interventionable');
        return ApiResponse::success($intervention, 'Intervention');
    }


    public function store(InterventionRequest $request, PictureUploadRequest $pictureUploadRequest)
    {
        try {
            DB::beginTransaction();

            $intervention = new Intervention(
                $request->validated()
            );

            if ($request->validated('ticket_id')) {
                $ticket = Ticket::find($request->validated('ticket_id'));
                $intervention->ticket()->associate($ticket);
                $intervention->interventionable()->associate($ticket->ticketable);
                $intervention->maintainable()->associate($ticket->ticketable->maintainable->id);
            } else {

                $modelMap = [
                    'sites' => \App\Models\Tenants\Site::class,
                    'buildings' => \App\Models\Tenants\Building::class,
                    'floors' => \App\Models\Tenants\Floor::class,
                    'rooms' => \App\Models\Tenants\Room::class,
                    'asset' => \App\Models\Tenants\Asset::class,
                    'providers' => \App\Models\Tenants\Provider::class,
                ];


                $model = $modelMap[$request->validated('locationType')];

                if ($model === Provider::class) {
                    $location = $model::where('id', $request->validated('locationId'))->first();
                    $intervention->interventionable()->associate($location);
                } else {
                    $location = $model::where('reference_code', $request->validated('locationId'))->first();
                    $intervention->interventionable()->associate($location);
                    $intervention->maintainable()->associate($location->maintainable->id);
                }
            }

            $intervention->interventionType()->associate($request->validated('intervention_type_id'));
            $intervention->save();

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
        try {
            DB::beginTransaction();

            $intervention->update([
                ...$request->safe()->except('locationType', 'locationId', 'ticket_id')
            ]);
            $intervention->interventionType()->associate($request->validated('intervention_type_id'));
            $intervention->save();

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
        $intervention->delete();
        return ApiResponse::success(null, 'Intervention deleted');
    }
}
