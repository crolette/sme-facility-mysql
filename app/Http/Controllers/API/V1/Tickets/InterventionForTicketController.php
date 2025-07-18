<?php

namespace App\Http\Controllers\API\V1\Tickets;

use Exception;
use App\Helpers\ApiResponse;
use App\Models\Tenants\Ticket;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Tenants\Intervention;
use App\Http\Requests\Tenant\InterventionRequest;

class InterventionForTicketController extends Controller
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
        try {
            DB::beginTransaction();

            $intervention = new Intervention(
                $request->validated()
            );

            $ticket = Ticket::find($request->validated('ticket_id'));
            $intervention->ticket()->associate($ticket);
            $intervention->interventionType()->associate($request->validated('intervention_type_id'));
            $intervention->interventionable()->associate($ticket->ticketable);
            $intervention->maintainable()->associate($ticket->ticketable->maintainable->id);

            $intervention->save();

            DB::commit();

            return ApiResponse::success(null, 'Intervention created');
        } catch (Exception $e) {
            DB::rollback();
            return ApiResponse::error('Error during Intervention creation', [$e->getMessage()]);
        }

        return ApiResponse::error('Error during Intervention creation');
    }

    public function update(InterventionRequest $request, Ticket $ticket)
    {
        try {
            DB::beginTransaction();

            // $ticket->update([
            //     ...$request->validated()
            // ]);

            // $models = [
            //     'assets'    => \App\Models\Tenants\Asset::class,
            //     'rooms'     => \App\Models\Tenants\Room::class,
            //     'floors'    => \App\Models\Tenants\Floor::class,
            //     'buildings' => \App\Models\Tenants\Building::class,
            //     'sites'     => \App\Models\Tenants\Site::class,
            // ];

            // if ($models[$request->validated('location_type')] !== get_class($ticket->ticketable)) {
            //     $location = $models[$request->validated('location_type')]::find($request->validated('location_id'));
            //     $ticket->ticketable()->dissociate();
            //     $ticket->ticketable()->associate($location);

            //     $ticket->save();
            // }



            DB::commit();
            return ApiResponse::success(null, 'Intervention updated');
        } catch (Exception $e) {
            DB::rollback();
            return ApiResponse::error('Error during Intervention update', [$e->getMessage()]);
        }

        return ApiResponse::error('Error during Intervention update');
    }
}
