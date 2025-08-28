<?php

namespace App\Http\Controllers\Tenants;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Tenants\User;
use App\Enums\NoticePeriodEnum;
use App\Models\Tenants\Contract;
use App\Enums\ContractStatusEnum;
use App\Enums\ContractDurationEnum;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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
        $contractDurations = array_column(ContractDurationEnum::cases(), 'value');
        $noticePeriods = array_column(NoticePeriodEnum::cases(), 'value');

        return Inertia::render('tenants/contracts/create', ['statuses' => $statuses, 'renewalTypes' => $renewalTypes, 'contractDurations' => $contractDurations, 'noticePeriods' => $noticePeriods]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function edit(Contract $contract)
    {
        $statuses = array_column(ContractStatusEnum::cases(), 'value');
        $renewalTypes = array_column(ContractRenewalTypesEnum::cases(), 'value');
        $contractDurations = array_column(ContractDurationEnum::cases(), 'value');
        $noticePeriods = array_column(NoticePeriodEnum::cases(), 'value');

        return Inertia::render('tenants/contracts/create', ['contract' => $contract->load('provider'), 'statuses' => $statuses, 'renewalTypes' => $renewalTypes, 'contractDurations' => $contractDurations, 'noticePeriods' => $noticePeriods, 'objects' => $contract->getObjects()]);
    }


    /**
     * Display the specified resource.
     */
    public function show(Contract $contract)
    {
        $preference = Auth::user()->notification_preferences()->where('notification_type', 'end_warranty_date')->first();
        // $preference = null;
        dd(!$preference);
        // dd(Contract::where('notice_date', '>', Carbon::now())->get());
        // dd($contract->end_date, $contract->end_date->subYears(5) < $contract->start_date);
        return Inertia::render('tenants/contracts/show', ['item' => $contract->load('provider'), 'objects' => $contract->getObjects()]);
    }
}
