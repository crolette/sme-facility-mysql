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
        $tickets = Ticket::all();

        return Inertia::render('tenants/tickets/index', ['tickets' => $tickets]);
    }

    public function create()
    {
        $statuses = array_column(TicketStatus::cases(), 'value');
        return Inertia::render('tenants/tickets/create', ['statuses' => $statuses]);
    }
};
