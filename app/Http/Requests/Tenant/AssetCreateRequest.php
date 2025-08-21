<?php

namespace App\Http\Requests\Tenant;

use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\Floor;
use Illuminate\Validation\Rule;
use App\Models\Tenants\Building;
use Illuminate\Validation\Validator;
use App\Models\Central\AssetCategory;
use App\Models\Central\CategoryType;
use Barryvdh\Debugbar\Facades\Debugbar;
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


        $this->replace($data);
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        // $data = $this->all();

        // $isCreate = $this->isMethod('post');
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
            "depreciation_start_date" => 'nullable|date|required_if_accepted:depreciation',
            "depreciation_end_date" => 'nullable|date',
            "depreciation_duration" => 'nullable|required_with:depreciation_start_date|numeric|gt:0',
            "residual_value" => 'nullable|numeric|decimal:0,2',
            "contract_end_date" => 'nullable|date',
            'model' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:50'],
        ];
    }
}
