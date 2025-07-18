<?php

namespace App\Http\Controllers\API\V1;

use Exception;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Ticket;
use App\Services\PictureService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Http\Requests\Tenant\TicketRequest;
use App\Http\Requests\Tenant\PictureUploadRequest;
use App\Http\Request\Tenant\InterventionRequest;

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
        try {
            DB::beginTransaction();



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
