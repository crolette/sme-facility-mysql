<?php

namespace App\Http\Controllers\Tenants;

use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Ticket;
use App\Models\Tenants\Contract;
use App\Models\Tenants\Provider;
use App\Enums\ContractStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;
use App\Enums\ContractRenewalTypesEnum;

class ContractController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contracts = Contract::select('id', 'name', 'type', 'provider_id', 'status', 'renewal_type', 'end_date')->with('provider:id,name,category_type_id')->get();
        $statuses = array_column(ContractStatusEnum::cases(), 'value');
        $renewalTypes = array_column(ContractRenewalTypesEnum::cases(), 'value');

        return Inertia::render('tenants/contracts/index', ['items' => $contracts, 'statuses' => $statuses, 'renewalTypes' => $renewalTypes]);
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
        // dd($contract->withObjects()->find($contract->id));
        return Inertia::render('tenants/contracts/create', ['contract' => $contract->load('provider'), 'statuses' => $statuses, 'renewalTypes' => $renewalTypes, 'objects' => $contract->getObjects()]);
    }


    /**
     * Display the specified resource.
     */
    public function show(Contract $contract)
    {
        return Inertia::render('tenants/contracts/show', ['item' => $contract->load('provider'), 'objects' => $contract->getObjects()]);
    }
}
