<?php

namespace App\Http\Requests\Tenant;

use Carbon\Carbon;
use App\Enums\NoticePeriodEnum;
use Illuminate\Validation\Rule;
use App\Enums\ContractStatusEnum;
use App\Enums\ContractDurationEnum;
use App\Enums\ContractRenewalTypesEnum;
use App\Enums\ContractTypesEnum;
use Illuminate\Foundation\Http\FormRequest;

class ContractUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation()
    {
        $data = $this->all();

        if (isset($data['start_date'])) {
            $endDate = ContractDurationEnum::from($data['contract_duration'])->addTo(Carbon::createFromFormat('Y-m-d', $data['start_date']));
        } else {
            $data['start_date'] = Carbon::now();
            $endDate = ContractDurationEnum::from($data['contract_duration'])->addTo(Carbon::now());
        }

        $data['end_date'] = $endDate;

        if (isset($data['notice_period'])) {
            $data['notice_date']  = NoticePeriodEnum::from($data['notice_period'])->subFrom($data['end_date']);
        }

        if (!isset($data['type'])) {
            $data['type']  = ContractTypesEnum::OTHER->value;
        }

        $this->replace($data);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        return [
            'provider_id' => 'required|exists:providers,id',
            'name' => 'required|string|min:4|max:100',
            'type' => ['nullable', Rule::in(array_column(ContractTypesEnum::cases(), 'value'))],
            'notes' => 'nullable|string|min:4|max:250',

            'internal_reference' => 'nullable|string|max:50',
            'provider_reference' => 'nullable|string|max:50',

            'start_date' => 'nullable|date',
            'contract_duration' => ['nullable', Rule::in(array_column(ContractDurationEnum::cases(), 'value'))],
            'end_date' => 'nullable|date',

            'notice_date' => 'nullable|date',
            'notice_period' => ['nullable', Rule::in(array_column(NoticePeriodEnum::cases(), 'value')), function ($attribute, $value, $fail) {
                if ($this->notice_date->toDateString() <= $this->start_date) {
                    $fail('Wrong notice period : Should be smaller than contract duration.');
                }
            }],

            'renewal_type' => ['required', Rule::in(array_column(ContractRenewalTypesEnum::cases(), 'value'))],
            'status' => ['required', Rule::in(array_column(ContractStatusEnum::cases(), 'value'))],

            'contractables' => 'nullable|array',
            'contractables.*.locationType' => 'required|in:site,building,floor,room,asset',
            'contractables.*.locationId' => ['required', 'integer'],
            'contractables.*.locationCode' => ['required', 'string'],
        ];
    }
}
