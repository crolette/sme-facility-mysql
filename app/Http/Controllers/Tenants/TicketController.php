<?php

namespace App\Http\Controllers\Tenants;

use Inertia\Inertia;
use App\Enums\TicketStatus;
use Illuminate\Http\Request;
use App\Models\Tenants\Ticket;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{

    public function index(Request $request)
    {
        if (Auth::user()->cannot('viewAny', Ticket::class))
            abort(403);

        $validator = Validator::make($request->all(), [
            'q' => 'string|max:255|nullable',
            'status' => 'string|nullable',
            'sortBy' => 'nullable|in:asc,desc',
            'orderBy' => 'string|nullable',
        ]);

        $validatedFields = $validator->validated();

        $statuses = array_column(TicketStatus::cases(), 'value');

        $tickets = Ticket::query();

        if (isset($validatedFields['q'])) {
            $tickets->where('description', 'like', '%' . $validatedFields['q'] . '%');
        }

        if (isset($validatedFields['status'])) {
            $tickets->where('status', $validatedFields['status']);
        }

        return Inertia::render('tenants/tickets/IndexTickets', ['items' => $tickets->orderBy($validatedFields['orderBy'] ?? 'created_at', $validatedFields['sortBy'] ?? 'asc')->paginate()->withQueryString(),  'filters' =>  $validator->safe()->only(['q', 'sortBy', 'status', 'orderBy']), 'statuses' => $statuses]);
    }


    public function show(Ticket $ticket)
    {

        if (Auth::user()->cannot('view', $ticket))
            abort(403);


        return Inertia::render('tenants/tickets/ShowTicket', ['item' => $ticket->load('pictures', 'interventions', 'closer')]);
    }
};
