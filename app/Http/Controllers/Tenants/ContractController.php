<?php

namespace App\Http\Controllers\Tenants;

use App\Enums\ContractRenewalTypesEnum;
use App\Enums\ContractStatusEnum;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Tenants\Ticket;
use App\Models\Tenants\Provider;
use App\Http\Controllers\Controller;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Contract;
use App\Models\Tenants\Intervention;

class ContractController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contracts = Contract::all();
        $statuses = array_column(ContractStatusEnum::cases(), 'value');
        $renewalTypes = array_column(ContractRenewalTypesEnum::cases(), 'value');

        return Inertia::render('tenants/contracts/index', ['contracts' => $contracts, 'statuses' => $statuses, 'renewalTypes' => $renewalTypes]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $statuses = array_column(ContractStatusEnum::cases(), 'value');
        $renewalTypes = array_column(ContractRenewalTypesEnum::cases(), 'value');

        return Inertia::render('tenants/contracts/create', ['statuses' => $statuses, 'renewalTypes' => $renewalTypes]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function edit(Contract $contract)
    {
        $statuses = array_column(ContractStatusEnum::cases(), 'value');
        $renewalTypes = array_column(ContractRenewalTypesEnum::cases(), 'value');
        return Inertia::render('tenants/contracts/create', ['contract' => $contract, 'statuses' => $statuses, 'renewalTypes' => $renewalTypes]);
    }


    /**
     * Display the specified resource.
     */
    public function show(Contract $contract)
    {
        return Inertia::render('tenants/contracts/show', ['contract' => $contract]);
    }
}
