<?php

namespace App\Http\Requests\Tenant;

use App\Enums\ContractDurationEnum;
use App\Enums\ContractRenewalTypesEnum;
use App\Enums\ContractStatusEnum;
use App\Enums\NoticePeriodEnum;
use Illuminate\Validation\Rule;
use App\Models\Tenants\Document;
use App\Models\Central\CategoryType;
use Illuminate\Foundation\Http\FormRequest;

class ContractStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    // public function prepareForValidation() 
    // {

    //     $data = $this->all();

    // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        $modelMap = [
            'site' => \App\Models\Tenants\Site::class,
            'building' => \App\Models\Tenants\Building::class,
            'floor' => \App\Models\Tenants\Floor::class,
            'room' => \App\Models\Tenants\Room::class,
            'asset' => \App\Models\Tenants\Asset::class,
        ];

        return [
            'provider_id' => 'required|exists:providers,id',
            'name' => 'required|string|min:4|max:100',
            'type' => 'nullable|string|min:4|max:100',
            'notes' => 'nullable|string|min:4|max:250',

            'internal_reference' => 'nullable|string|max:50',
            'provider_reference' => 'nullable|string|max:50',

            'start_date' => 'nullable|date',
            'contract_duration' => ['nullable', Rule::in(array_column(ContractDurationEnum::cases(), 'value'))],
            'end_date' => 'nullable|date',

            'notice_period' => ['nullable', Rule::in(array_column(NoticePeriodEnum::cases(), 'value'))],

            'renewal_type' => ['required', Rule::in(array_column(ContractRenewalTypesEnum::cases(), 'value'))],
            'status' => ['required', Rule::in(array_column(ContractStatusEnum::cases(), 'value'))],

            'contractables' => 'nullable|array',
            'contractables.*.locationType' => 'required|in:site,building,floor,room,asset',
            'contractables.*.locationId' => ['required', 'integer'],
            'contractables.*.locationCode' => ['required', 'string'],

        ];
    }
}
