<?php

namespace App\Http\Requests\Tenant;

use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Enums\MeterReadingsUnits;
use App\Models\Central\CategoryType;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class AssetUpdateRequest extends FormRequest
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

        isset($data['has_meter_readings']) && ($data['has_meter_readings'] === 'true' || $data['has_meter_readings'] === true) ? $data['has_meter_readings'] = true : $data['has_meter_readings'] = false;

        if ($data['has_meter_readings'] === false) {
            $data['meter_number'] = null;
            $data['meter_unit'] = null;
        }

        isset($data['need_qr_code']) && ($data['need_qr_code'] === 'true' || $data['need_qr_code'] === true) ? $data['need_qr_code'] = true : $data['need_qr_code'] = false;
        isset($data['is_mobile']) && ($data['is_mobile'] === 'true' || $data['is_mobile'] === true) ? $data['is_mobile'] = true : $data['is_mobile'] = false;
        isset($data['depreciable']) && ($data['depreciable'] === 'true' || $data['depreciable'] === true) ? $data['depreciable'] = true : $data['depreciable'] = false;

        if ($data['depreciable'] === false) {
            $data['depreciation_start_date'] = null;
            $data['depreciation_end_date'] = null;
            $data['depreciation_duration'] = null;
            $data['residual_value'] = null;
            $data['accounting_reference'] = null;
        }

        if ($data['depreciable'] === true && $data['depreciation_end_date'] === null) {
            $data['depreciation_end_date'] = Carbon::createFromFormat('Y-m-d', $data['depreciation_start_date'])->addYears($data['depreciation_duration']);
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

        $data = $this->all();

        $rules = [
            'need_qr_code' => 'sometimes|boolean',
            'is_mobile' => 'sometimes|boolean',
            'locationType' => ['nullable', 'in:user,site,building,floor,room'],
            'locationId' => ['nullable'],
            'surface' => 'nullable|numeric|gt:0|decimal:0,2',
            'depreciable' => "boolean",
            "depreciation_start_date" => 'nullable|date|required_if_accepted:depreciation',
            "depreciation_end_date" => 'nullable|date',
            "depreciation_duration" => 'nullable|required_with:depreciation_start_date|numeric|gt:0',
            "accounting_reference" => 'nullable|string|max:25',
            "residual_value" => 'nullable|numeric|decimal:0,2',
            'categoryId' => ['required', Rule::in(CategoryType::where('category', 'asset')->pluck('id')->toArray())],
            'model' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:50'],
            'has_meter_readings' => 'boolean',
            'meter_number' => 'nullable|string|max:20',
            'meter_unit' => ['nullable', Rule::in(array_column(MeterReadingsUnits::cases(), 'value'))]
        ];

        $isCreate = $this->isMethod('post');
        $type = $this->input('locationType');


        if ($isCreate && $type === null) {
            throw ValidationException::withMessages([
                'locationType' => __('validation.required', ['attribute' => 'location type'])
            ]);;
        }

        if ($type !== null && !in_array($type, ['user', 'site', 'building', 'floor', 'room'], true)) {
            throw ValidationException::withMessages([
                'locationType' => __('validation.in', ['attribute' => 'location type'])
            ]);
        }




        if (in_array($type, ['user', 'site', 'building', 'floor', 'room'], true)) {
            $modelMap = [
                'user' => \App\Models\Tenants\User::class,
                'site' => \App\Models\Tenants\Site::class,
                'building' => \App\Models\Tenants\Building::class,
                'floor' => \App\Models\Tenants\Floor::class,
                'room' => \App\Models\Tenants\Room::class,
            ];

            $model = $modelMap[$type];
        }

        // TODO Required_with rule ?

        if ($isCreate) {
            $rules['locationType'] = ['required', 'in:user,site,building,floor,room'];
            $rules['locationId'] = ['required', Rule::in($model::pluck('id')->toArray())];
        } else {
            if ($this->filled('locationType') || $this->filled('locationId')) {
                $rules['locationType'] = ['required', 'in:user,site,building,floor,room'];
                $rules['locationId'] = ['required', Rule::in($model::pluck('id')->toArray())];
            }
        }

        return $rules;
    }
}
