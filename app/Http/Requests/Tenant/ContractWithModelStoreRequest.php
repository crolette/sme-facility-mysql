<?php

namespace App\Http\Requests\Tenant;

use App\Enums\ContractRenewalTypesEnum;
use App\Enums\ContractStatusEnum;
use Illuminate\Validation\Rule;
use App\Models\Tenants\Document;
use App\Models\Central\CategoryType;
use Illuminate\Foundation\Http\FormRequest;

class ContractWithModelStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        return [
            'contracts' => 'nullable|array',
            'contracts.*.provider_id' => 'required|exists:providers,id',
            'contracts.*.name' => 'required|string|min:4|max:100',
            'contracts.*.type' => 'nullable|string|min:4|max:100',
            'contracts.*.notes' => 'nullable|string|min:4|max:250',

            'contracts.*.internal_reference' => 'nullable|string|max:50',
            'contracts.*.provider_reference' => 'nullable|string|max:50',

            'contracts.*.start_date' => 'nullable|date',
            'contracts.*.end_date' => 'nullable|date',

            'contracts.*.renewal_type' => ['required', Rule::in(array_column(ContractRenewalTypesEnum::cases(), 'value'))],
            'contracts.*.status' => ['required', Rule::in(array_column(ContractStatusEnum::cases(), 'value'))],

        ];
    }
}
