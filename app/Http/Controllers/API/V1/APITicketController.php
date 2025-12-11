<?php

namespace App\Http\Controllers\API\V1;

use Exception;
use Carbon\Carbon;
use App\Events\TicketClosed;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Events\TicketCreated;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Ticket;
use App\Models\Tenants\Company;
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

        if ($status !== null) {
            $tickets = Ticket::where('status', $status)->get();
        } else {
            $tickets = Ticket::all();
        }

        return ApiResponse::success($tickets, 'Ticket index');
    }

    public function show(Ticket $ticket)
    {
        return ApiResponse::success($ticket->load('pictures'), 'Ticket');
    }

    public function store(TicketRequest $request, PictureUploadRequest $pictureUploadRequest, PictureService $pictureService)
    {
        try {
            DB::beginTransaction();

            $count = Company::incrementAndGetTicketNumber();
            $codeNumber = generateCodeNumber($count, 'TK', 4);
            $ticket = new Ticket(
                [
                    ...$request->validated(),
                    'code' => $codeNumber
                ]
            );
            if ($request->validated('reporter_email')) {
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

            $location = $models[$request->validated('location_type')]::where('reference_code', $request->validated('location_code'))->first();

            $ticket->ticketable()->associate($location);
            $ticket->save();

            // TODO Send email to admin / maintenance manager if not the one who created the ticket

            $files = $pictureUploadRequest->validated('pictures');

            if ($files) {
                $pictureService->uploadAndAttachPictures($ticket, $files, $request->validated('reporter_email') ?? null);
            }


            DB::commit();

            event(new TicketCreated($ticket, $location));

            return ApiResponse::success(null, 'Ticket created');
        } catch (Exception $e) {
            DB::rollback();
            return ApiResponse::error('Error during Ticket creation', [$e->getMessage()]);
        }
        return ApiResponse::error('Error during Ticket creation');
    }

    public function update(TicketRequest $request, Ticket $ticket)
    {
        if (Auth::user()->cannot('update', $ticket))
            return ApiResponse::notAuthorized();

        try {
            DB::beginTransaction();

            $ticket->update([
                ...$request->validated()
            ]);

            if ($request->status === 'ongoing' && !$ticket->handled_at) {
                $ticket->update(['handled_at' => Carbon::now()]);
            }

            $models = [
                'assets'    => \App\Models\Tenants\Asset::class,
                'rooms'     => \App\Models\Tenants\Room::class,
                'floors'    => \App\Models\Tenants\Floor::class,
                'buildings' => \App\Models\Tenants\Building::class,
                'sites'     => \App\Models\Tenants\Site::class,
            ];

            if ($models[$request->validated('location_type')] !== get_class($ticket->ticketable)) {
                $location = $models[$request->validated('location_type')]::where('code', $request->validated('location_code'))->first();
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

    public function changeStatus(Request $request, Ticket $ticket)
    {
        if (Auth::user()->cannot('update', $ticket))
            return ApiResponse::notAuthorized();


        if (in_array($request->status, ['open', 'closed', 'ongoing'])) {
            if ($request->status === 'closed') {
                $ticket->closeTicket();


                return ApiResponse::success(null, 'Ticket closed');
            }

            // TODO Check if ok with tests
            if ($request->status === 'ongoing' && !$ticket->handled_at) {
                $ticket->update(['handled_at' => Carbon::now()]);
            }

            $ticket->update(['status' => $request->status]);
            return ApiResponse::success(null, 'Ticket updated');
        }

        return ApiResponse::error('Error during Ticket update');
    }

    public function destroy(Ticket $ticket)
    {
        if (Auth::user()->cannot('delete', $ticket))
            return ApiResponse::notAuthorized();

        try {
            $ticket->delete();
            return ApiResponse::success(null, 'Ticket deleted');
        } catch (Exception $e) {
            return ApiResponse::error('Error during Ticket deletion', [$e->getMessage()]);
        }

        return ApiResponse::error('Error during Ticket deletion');
    }



    // public function close(Ticket $ticket)
    // {
    //     try {
    //         $response = $ticket->closeTicket();
    //         return ApiResponse::success(null, 'Ticket closed');
    //     } catch (Exception $e) {
    //         return ApiResponse::error('Error during Ticket closing', [$e->getMessage()]);
    //     }

    //     return ApiResponse::error('Error during Ticket closing');
    // }
}
