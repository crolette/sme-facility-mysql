<?php

namespace App\Http\Controllers\Tenants;

use Inertia\Inertia;
use App\Enums\TicketStatus;
use Illuminate\Http\Request;
use App\Models\Tenants\Ticket;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{

    public function index()
    {
        if(Auth::user()->cannot('viewAny', Ticket::class))
            abort(403);


        return Inertia::render('tenants/tickets/index');
    }

    
    public function show(Ticket $ticket)
    {

        if (Auth::user()->cannot('view', $ticket))
            abort(403);
        

        return Inertia::render('tenants/tickets/show', ['ticket' => $ticket->load('pictures', 'interventions')]);
    }
};
