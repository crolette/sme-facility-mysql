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
use App\Enums\ContractTypesEnum;
use App\Models\Central\CategoryType;
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
        $contractTypes = array_column(ContractTypesEnum::cases(), 'value');
        $renewalTypes = array_column(ContractRenewalTypesEnum::cases(), 'value');
        $providerCategories = CategoryType::getByCategoryCache('provider');

        // dd($providerCategories);

        $validator = Validator::make($request->all(), [
            'q' => 'string|max:255|nullable',
            'category' => 'integer|nullable|gt:0',
            'orderBy' => 'in:asc,desc',
            'sortBy' => 'string|nullable',
            'type' => 'string|nullable',
            'status' => 'string|nullable',
            'provider_category_id' => 'integer|exists:central.category_types,id',
            'renewalType' => 'string|nullable',
        ]);

        $validatedFields = $validator->validated();

        $contracts = Contract::select('id', 'name', 'type', 'provider_id', 'status', 'renewal_type', 'end_date', 'internal_reference', 'provider_reference')->with('provider:id,name')->forMaintenanceManager();



        if (isset($validatedFields['renewalType'])) {
            $contracts->where('renewal_type', $validatedFields['renewalType']);
        }

        if (isset($validatedFields['status'])) {
            $contracts->where('status', $validatedFields['status']);
        }

        if (isset($validatedFields['type'])) {
            $contracts->where('type', $validatedFields['type']);
        }

        if (isset($validatedFields['q'])) {
            $contracts->where(function (Builder $query) use ($validatedFields) {
                $query->where('name', 'like', '%' . $validatedFields['q'] . '%')
                    ->orWhere('internal_reference', 'like', '%' . $validatedFields['q'] . '%')
                    ->orWhere('provider_reference', 'like', '%' . $validatedFields['q'] . '%');
            })->orWhereHas('provider', function (Builder $query) use ($validatedFields) {
                $query->where('name', 'like', '%' . $validatedFields['provider'] . '%');
            });
        }

        if (isset($validatedFields['provider_category_id'])) {
            // $contracts->whereHas('provider', function (Builder $query) use ($validatedFields) {
            //     $query->where('category_type_id', $validatedFields['provider_category_id']);
            // });

            $contracts->whereHas('provider', function (Builder $query) use ($validatedFields) {
                $query->whereIn('id', function ($query) use ($validatedFields) {
                    $query->select('provider_id')
                        ->from('category_type_provider')
                        ->where('category_type_id', $validatedFields['provider_category_id']);
                });
            });
        }


        return Inertia::render('tenants/contracts/IndexContracts', ['items' => $contracts->orderBy($validatedFields['sortBy'] ?? 'end_date', $validatedFields['orderBy'] ?? 'asc')->paginate()->withQueryString(), 'filters' =>  $validator->safe()->only(['q', 'type', 'status', 'orderBy', 'sortBy', 'provider_category_id', 'renewalType']), 'statuses' => $statuses, 'contractTypes' => $contractTypes, 'renewalTypes' => $renewalTypes, 'providerCategories' => $providerCategories]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        if (Auth::user()->cannot('create', Contract::class))
            abort(403);

        $statuses = array_column(ContractStatusEnum::cases(), 'value');
        $contractTypes = array_column(ContractTypesEnum::cases(), 'value');
        $renewalTypes = array_column(ContractRenewalTypesEnum::cases(), 'value');
        $contractDurations = array_column(ContractDurationEnum::cases(), 'value');
        $noticePeriods = array_column(NoticePeriodEnum::cases(), 'value');

        return Inertia::render('tenants/contracts/CreateUpdateContract', ['statuses' => $statuses, 'renewalTypes' => $renewalTypes, 'contractDurations' => $contractDurations, 'noticePeriods' => $noticePeriods, 'contractTypes' => $contractTypes]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function edit(Contract $contract)
    {
        if (Auth::user()->cannot('update', $contract))
            abort(403);

        $statuses = array_column(ContractStatusEnum::cases(), 'value');
        $contractTypes = array_column(ContractTypesEnum::cases(), 'value');
        $renewalTypes = array_column(ContractRenewalTypesEnum::cases(), 'value');
        $contractDurations = array_column(ContractDurationEnum::cases(), 'value');
        $noticePeriods = array_column(NoticePeriodEnum::cases(), 'value');

        return Inertia::render('tenants/contracts/CreateUpdateContract', ['contract' => $contract->load('provider'), 'statuses' => $statuses, 'renewalTypes' => $renewalTypes, 'contractDurations' => $contractDurations, 'noticePeriods' => $noticePeriods, 'objects' => $contract->getObjects(), 'contractTypes' => $contractTypes]);
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
