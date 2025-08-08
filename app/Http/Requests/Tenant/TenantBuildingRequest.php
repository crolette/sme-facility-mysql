<?php

namespace App\Http\Requests\Tenant;

use App\Models\LocationType;
use App\Models\Tenants\Site;
use Illuminate\Validation\Rule;
use App\Models\Central\CategoryType;
use Illuminate\Foundation\Http\FormRequest;

class TenantBuildingRequest extends FormRequest
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

        if (isset($data['locationTypeName']) && $data['locationTypeName'] === 'outdoor') {
            $data['surface_floor'] = null;
            $data['floor_material_id'] = null;
            $data['floor_material_other'] = null;
            $data['surface_walls'] = null;
            $data['wall_material_id'] = null;
            $data['wall_material_other'] = null;
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
        $siteTypes = Site::all()->pluck('id');
        $locationTypes = LocationType::where('level', 'building')->pluck('id');

        return [
            'need_qr_code' => 'sometimes|boolean',
            'levelType' => ['required', Rule::in([...$siteTypes])],
            'locationType' => ['required', Rule::in([...$locationTypes])],
            'surface_floor' => 'nullable|numeric|gt:0|decimal:0,2',
            'locationTypeName' => 'sometimes',
            'floor_material_id' => ['nullable', Rule::anyOf([Rule::in(CategoryType::where('category', 'floor_materials')->pluck('id')->toArray()), Rule::in('other')])],
            'floor_material_other' => ['nullable', 'required_if:floor_material_id,other'],
            'surface_walls' => 'nullable|numeric|gt:0|decimal:0,2',
            'wall_material_id' => ['nullable', Rule::anyOf([Rule::in(CategoryType::where('category', 'wall_materials')->pluck('id')->toArray()), Rule::in('other')])],
            'wall_material_other' => ['nullable', 'required_if:wall_material_id,other'],
            'surface_outdoor' => ['nullable', Rule::when(fn($input) => $input->locationTypeName === 'outdoor', [
                'numeric',
                'gt:0',
                'decimal:0,2'
            ])],
            'outdoor_material_id' => ['nullable', Rule::anyOf([Rule::in(CategoryType::where('category', 'outdoor_materials')->pluck('id')->toArray()), Rule::in('other')])],
            'outdoor_material_other' => ['nullable', 'required_if:outdoor_material_id,other'],
        ];
    }
}
