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

class APITicketController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        if ($status != null) {
            $tickets = Ticket::where('status', $status)->get()->load('pictures');
        } else {
            $tickets = Ticket::all()->load('pictures');
        }
        return ApiResponse::success($tickets, 'Ticket created');
    }

    public function show(Ticket $ticket)
    {
        return ApiResponse::success($ticket->load('pictures'), 'Ticket');
    }

    public function store(TicketRequest $request, PictureUploadRequest $pictureUploadRequest, PictureService $pictureService)
    {

        try {
            DB::beginTransaction();

            $count = Ticket::all()->count();
            $codeNumber = generateCodeNumber($count, 'TK', 4);

            $ticket = new Ticket(
                [
                    ...$request->validated(),
                    'code' => $codeNumber
                ]
            );


            if (!$request->validated('reporter_email')) {
                $ticket->reporter()->associate($request->validated('reported_by'));
            } else {
                $ticket->reporter()->associate(Auth::guard('tenant')->user()->id);
            }

            $models = [
                'assets'    => \App\Models\Tenants\Asset::class,
                'rooms'     => \App\Models\Tenants\Room::class,
                'floors'    => \App\Models\Tenants\Floor::class,
                'buildings' => \App\Models\Tenants\Building::class,
                'sites'     => \App\Models\Tenants\Site::class,
            ];

            $location = $models[$request->validated('location_type')]::find($request->validated('location_id'));

            $ticket->ticketable()->associate($location);

            $ticket->save();

            $files = $pictureUploadRequest->validated('pictures');

            if ($files) {
                $pictureService->uploadAndAttachPictures($ticket, $files, $request->validated('reporter_email') ?? null);
            }

            DB::commit();

            return ApiResponse::success(null, 'Ticket created');
        } catch (Exception $e) {
            DB::rollback();
            return ApiResponse::error('Error during Ticket creation', [$e->getMessage()]);
        }

        return ApiResponse::error('Error during Ticket creation');
    }

    public function update(TicketRequest $request, Ticket $ticket)
    {
        try {
            DB::beginTransaction();

            $ticket->update([
                ...$request->validated()
            ]);

            $models = [
                'assets'    => \App\Models\Tenants\Asset::class,
                'rooms'     => \App\Models\Tenants\Room::class,
                'floors'    => \App\Models\Tenants\Floor::class,
                'buildings' => \App\Models\Tenants\Building::class,
                'sites'     => \App\Models\Tenants\Site::class,
            ];

            if ($models[$request->validated('location_type')] !== get_class($ticket->ticketable)) {
                $location = $models[$request->validated('location_type')]::find($request->validated('location_id'));
                $ticket->ticketable()->dissociate();
                $ticket->ticketable()->associate($location);

                $ticket->save();
            }



            DB::commit();
            return ApiResponse::success(null, 'Ticket updated');
        } catch (Exception $e) {
            DB::rollback();
            return ApiResponse::error('Error during Ticket update', [$e->getMessage()]);
        }

        return ApiResponse::error('Error during Ticket update');
    }


    public function close(Ticket $ticket)
    {
        try {
            $response = $ticket->closeTicket();
            return ApiResponse::success(null, 'Ticket closed');
        } catch (Exception $e) {
            return ApiResponse::error('Error during Ticket closing', [$e->getMessage()]);
        }

        return ApiResponse::error('Error during Ticket closing');
    }
}
