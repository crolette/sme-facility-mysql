<?php

namespace App\Http\Controllers\Tenants;

use Inertia\Inertia;
use App\Enums\PriorityLevel;
use Illuminate\Http\Request;
use App\Models\Tenants\Ticket;
use App\Enums\InterventionStatus;
use App\Http\Controllers\Controller;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;
use Illuminate\Support\Facades\Validator;

class InterventionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'string|max:255|nullable',
            'sortBy' => 'in:asc,desc',
            'orderBy' => 'string|nullable',
            'status' => 'string|nullable',
            'priority' => 'string|nullable',
            'type' => 'nullable|integer|gt:0'
        ]);

        $validatedFields = $validator->validated();
        $interventions = Intervention::with('interventionable');

        if (isset($validatedFields['status'])) {
            $interventions->where('status', $validatedFields['status']);
        };

        if (isset($validatedFields['priority'])) {
            $interventions->where('priority', $validatedFields['priority']);
        };

        if (isset($validatedFields['type'])) {
            $interventions->where('intervention_type_id', $validatedFields['type']);
        };

        if (isset($validatedFields['q'])) {
            $interventions->where('description', 'like', '%' . $validatedFields['q'] . '%');
        }

        $priorities = array_column(PriorityLevel::cases(), 'value');
        $statuses = array_column(InterventionStatus::cases(), 'value');
        $types = CategoryType::where('category', 'intervention')->get();

        return Inertia::render('tenants/interventions/IndexInterventions', ['items' => $interventions->orderBy($validatedFields['orderBy'] ?? 'planned_at', $validatedFields['sortBy'] ?? 'asc')->paginate()->withQueryString(), 'filters' =>  $validator->safe()->only(['q', 'sortBy', 'status', 'orderBy', 'type', 'priority']), 'priorities' => $priorities, 'types' => $types, 'statuses' => $statuses]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Ticket $ticket)
    {
        return Inertia::render('tenants/tickets/interventions/create', ['ticket' => $ticket]);
    }

    /**
     * Show the form for creating a new resource.
     */
    // public function edit(Intervention $intervention)
    // {
    //     return Inertia::render('tenants/tickets/interventions/create', ['ticket' => $ticket]);
    // }


    /**
     * Display the specified resource.
     */
    public function show(Intervention $intervention)
    {
        $statuses = array_column(InterventionStatus::cases(), 'value');

        return Inertia::render('tenants/tickets/interventions/ShowIntervention', ['intervention' => $intervention->load(['ticket', 'interventionable', 'pictures', 'actions.pictures']), 'statuses' => $statuses]);
    }
}
