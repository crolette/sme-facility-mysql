<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Validation\Rule;
use App\Models\Central\CategoryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class AssetCreateRequest extends FormRequest
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

        isset($data['need_qr_code']) && ($data['need_qr_code'] === 'true' || $data['need_qr_code'] === true) ? $data['need_qr_code'] = true : $data['need_qr_code'] = false;
        isset($data['is_mobile']) && ($data['is_mobile'] === 'true' || $data['is_mobile'] === true) ? $data['is_mobile'] = true : $data['is_mobile'] = false;
        isset($data['depreciable']) && ($data['depreciable'] === 'true' || $data['depreciable'] === true) ? $data['depreciable'] = true : $data['depreciable'] = false;


        if ($data['depreciable'] === false) {
            $data['depreciation_start_date'] = null;
            $data['depreciation_end_date'] = null;
            $data['depreciation_duration'] = null;
            $data['residual_value'] = null;
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

        $type = $this->input('locationType');


        if (in_array($type, ['user', 'site', 'building', 'floor', 'room'], true)) {
            $modelMap = [
                'user' => \App\Models\Tenants\User::class,
                'site' => \App\Models\Tenants\Site::class,
                'building' => \App\Models\Tenants\Building::class,
                'floor' => \App\Models\Tenants\Floor::class,
                'room' => \App\Models\Tenants\Room::class,
            ];

            $model = $modelMap[$type];
        } else {
            throw ValidationException::withMessages([
                'locationType' => __('validation.in', ['attribute' => 'location type'])
            ]);
        }

        return [
            'locationType' => ['required', 'in:user,site,building,floor,room'],
            'locationId' => ['required', Rule::in($model::pluck('id')->toArray())],
            'categoryId' => ['required', Rule::in(CategoryType::where('category', 'asset')->pluck('id')->toArray())],
            'need_qr_code' => 'sometimes|boolean',
            'is_mobile' => 'sometimes|boolean',
            'surface' => 'nullable|numeric|gt:0|decimal:0,2',
            'depreciable' => "boolean",
            "depreciation_start_date" => 'nullable|date|required_if_accepted:depreciable',
            "depreciation_end_date" => 'nullable|date|after:depreciation_start_date',
            "depreciation_duration" => 'nullable|required_with:depreciation_start_date|numeric|gt:0',
            "residual_value" => 'nullable|numeric|decimal:0,2',
            'model' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:50'],
        ];
    }
}
