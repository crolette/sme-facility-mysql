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

class APIInterventionController extends Controller
{
    // public function index(Request $request)
    // {
    //     $status = $request->query('status');
    //     if ($status != null) {
    //         $tickets = Ticket::where('status', $status)->get()->load('pictures');
    //     } else {
    //         $tickets = Ticket::all()->load('pictures');
    //     }
    //     return ApiResponse::success($tickets, 'Ticket created');
    // }

    // public function show(Ticket $ticket)
    // {
    //     return ApiResponse::success($ticket->load('pictures'), 'Ticket');
    // }

    public function store(InterventionRequest $request)
    {
        Debugbar::info('store intervention', $request->validated());
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
                ];

                $model = $modelMap[$request->validated('locationType')];
                $location = $model::where('reference_code', $request->validated('locationId'))->first();
                $intervention->interventionable()->associate($location);
                $intervention->maintainable()->associate($location->maintainable->id);
            }

            $intervention->interventionType()->associate($request->validated('intervention_type_id'));
            $intervention->save();

            DB::commit();

            return ApiResponse::success(null, 'Intervention created');
        } catch (Exception $e) {
            DB::rollback();
            return ApiResponse::error('Error during Intervention creation', [$e->getMessage()]);
        }

        return ApiResponse::error('Error during Intervention creation');
    }

    public function update(InterventionRequest $request, Intervention $intervention)
    {
        try {
            DB::beginTransaction();

            $intervention->update([
                ...$request->safe()->except('locationType', 'locationId', 'ticket_id')
            ]);

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
