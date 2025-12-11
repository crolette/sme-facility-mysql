<?php

namespace App\Http\Requests\Tenant;

use Carbon\Carbon;
use App\Enums\NoticePeriodEnum;
use Illuminate\Validation\Rule;
use App\Models\Tenants\Document;
use App\Enums\ContractStatusEnum;
use App\Enums\ContractDurationEnum;
use App\Models\Central\CategoryType;
use App\Enums\ContractRenewalTypesEnum;
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

    public function prepareForValidation()
    {
        $data = $this->all();

        if (isset($data['contracts'])) {
            foreach ($data['contracts'] as $index => $contract) {

                if (isset($contract['start_date'])) {
                    $endDate = ContractDurationEnum::from($contract['contract_duration'])->addTo(Carbon::createFromFormat('Y-m-d', $contract['start_date']));
                } else {
                    $data['contracts'][$index]['start_date'] = Carbon::now();
                    $endDate = ContractDurationEnum::from($contract['contract_duration'])->addTo(Carbon::now());
                }

                $data['contracts'][$index]['end_date'] = $endDate;

                // dump($contract['notice_period']);
                if (isset($contract['notice_period'])) {
                    $data['contracts'][$index]['notice_date']  = NoticePeriodEnum::from($contract['notice_period'])->subFrom($data['contracts'][$index]['end_date']);
                    // dump($contract['notice_date']);
                }

                if (!isset($contract['notice_period']))
                    $data['contracts'][$index]['notice_date'] = null;
            }
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
            'contracts' => 'nullable|array',
            'contracts.*.provider_id' => 'required|exists:providers,id',
            'contracts.*.name' => 'required|string|min:4|max:100',
            'contracts.*.type' => 'nullable|string|min:4|max:100',
            'contracts.*.notes' => 'nullable|string|min:4|max:250',

            'contracts.*.internal_reference' => 'nullable|string|max:50',
            'contracts.*.provider_reference' => 'nullable|string|max:50',

            'contracts.*.start_date' => 'nullable|date',
            'contracts.*.contract_duration' => ['nullable', Rule::in(array_column(ContractDurationEnum::cases(), 'value'))],
            'contracts.*.end_date' => 'nullable|date',

            'contracts.*.notice_period' => ['nullable', Rule::in(array_column(NoticePeriodEnum::cases(), 'value'))],
            'contracts.*.notice_date' => ['nullable', 'date'],

            'contracts.*.renewal_type' => ['required', Rule::in(array_column(ContractRenewalTypesEnum::cases(), 'value'))],
            'contracts.*.status' => ['required', Rule::in(array_column(ContractStatusEnum::cases(), 'value'))],

            'existing_contracts' => 'nullable|array',
            'existing_contracts.*' => 'required|exists:contracts,id',

            'contracts.*.files' => 'nullable|array',
            'contracts.*.files.*.file' => 'required_with:files.*.name|file|mimes:jpg,jpeg,png,pdf|max:' . Document::maxUploadSizeKB(),
            'contracts.*.files.*.name' => 'required_with:files.*.file|string|min:10|max:100',
            'contracts.*.files.*.description' => 'nullable|string|min:10|max:250',
            'contracts.*.files.*.typeId' => ['required_with:files.*.name', Rule::in(CategoryType::where('category', 'document')->pluck('id')->toArray())],
            'contracts.*.files.*.typeSlug' => ['required_with:files.*.name', Rule::in(CategoryType::where('category', 'document')->pluck('slug')->toArray())],

        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (isset($this->contracts)) {
                foreach ($this->contracts as $index => $contract) {

                    // dump($contract);
                    // dump($contract['notice_date']);
                    $noticeDate = Carbon::parse($contract['notice_date']);
                    $startDate = Carbon::parse($contract['start_date']);

                    if ($noticeDate <= $startDate) {
                        $validator->errors()->add(
                            "contracts.{$index}.notice_period",
                            'Wrong notice period : Should be smaller than contract duration.'
                        );
                    }
                }
            }
        });
    }
}
