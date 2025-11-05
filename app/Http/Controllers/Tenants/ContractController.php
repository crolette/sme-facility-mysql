<?php

namespace App\Http\Controllers\Tenants;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Tenants\User;
use Illuminate\Http\Request;
use App\Enums\NoticePeriodEnum;
use App\Models\Tenants\Contract;
use App\Enums\ContractStatusEnum;
use App\Enums\ContractDurationEnum;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Enums\ContractRenewalTypesEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;

class ContractController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        if (Auth::user()->cannot('viewAny', Contract::class))
            abort(403);

        $statuses = array_column(ContractStatusEnum::cases(), 'value');
        $renewalTypes = array_column(ContractRenewalTypesEnum::cases(), 'value');

        $validator = Validator::make($request->all(), [
            'q' => 'string|max:255|nullable',
            'category' => 'integer|nullable|gt:0',
            'sortBy' => 'in:asc,desc',
            'orderBy' => 'string|nullable',
            'type' => 'string|nullable',
            'status' => 'string|nullable',
            'provider' => 'string|nullable',
            'renewalType' => 'string|nullable',
        ]);

        $validatedFields = $validator->validated();

        $contracts = Contract::select('id', 'name', 'type', 'provider_id', 'status', 'renewal_type', 'end_date', 'internal_reference', 'provider_reference')->with('provider:id,name,category_type_id');

        if (isset($validatedFields['renewalType'])) {
            $contracts->where('renewal_type', $validatedFields['renewalType']);
        }

        if (isset($validatedFields['status'])) {
            $contracts->where('status', $validatedFields['status']);
        }

        if (isset($validatedFields['q'])) {
            $contracts->where(function (Builder $query) use ($validatedFields) {
                $query->where('name', 'like', '%' . $validatedFields['q'] . '%')
                    ->orWhere('internal_reference', 'like', '%' . $validatedFields['q'] . '%')
                    ->orWhere('provider_reference', 'like', '%' . $validatedFields['q'] . '%');
            });
        }

        if (isset($validatedFields['provider'])) {
            $contracts->whereHas('provider', function (Builder $query) use ($validatedFields) {
                $query->where('name', 'like', '%' . $validatedFields['provider'] . '%');
            });
        }


        return Inertia::render('tenants/contracts/IndexContracts', ['items' => $contracts->orderBy($validatedFields['orderBy'] ?? 'end_date', $validatedFields['sortBy'] ?? 'asc')->paginate()->withQueryString(), 'filters' =>  $validator->safe()->only(['q', 'type', 'status', 'orderBy', 'sortBy', 'provider', 'renewalType']), 'statuses' => $statuses, 'renewalTypes' => $renewalTypes]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        if (Auth::user()->cannot('create', Contract::class))
            abort(403);

        $statuses = array_column(ContractStatusEnum::cases(), 'value');
        $renewalTypes = array_column(ContractRenewalTypesEnum::cases(), 'value');
        $contractDurations = array_column(ContractDurationEnum::cases(), 'value');
        $noticePeriods = array_column(NoticePeriodEnum::cases(), 'value');

        return Inertia::render('tenants/contracts/CreateUpdateContract', ['statuses' => $statuses, 'renewalTypes' => $renewalTypes, 'contractDurations' => $contractDurations, 'noticePeriods' => $noticePeriods]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function edit(Contract $contract)
    {
        if (Auth::user()->cannot('update', $contract))
            abort(403);

        $statuses = array_column(ContractStatusEnum::cases(), 'value');
        $renewalTypes = array_column(ContractRenewalTypesEnum::cases(), 'value');
        $contractDurations = array_column(ContractDurationEnum::cases(), 'value');
        $noticePeriods = array_column(NoticePeriodEnum::cases(), 'value');

        return Inertia::render('tenants/contracts/CreateUpdateContract', ['contract' => $contract->load('provider'), 'statuses' => $statuses, 'renewalTypes' => $renewalTypes, 'contractDurations' => $contractDurations, 'noticePeriods' => $noticePeriods, 'objects' => $contract->getObjects()]);
    }


    /**
     * Display the specified resource.
     */
    public function show(Contract $contract)
    {
        if (Auth::user()->cannot('view', $contract))
            abort(403);
        // dd(Contract::where('notice_date', '>', Carbon::now())->get());
        // dd($contract->end_date, $contract->end_date->subYears(5) < $contract->start_date);
        return Inertia::render('tenants/contracts/ShowContract', ['item' => $contract->load('provider', 'documents'), 'objects' => $contract->getObjects()]);
    }
}
