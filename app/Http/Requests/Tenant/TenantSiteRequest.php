<?php

namespace App\Http\Requests\Tenant;

use App\Models\LocationType;
use Illuminate\Validation\Rule;
use App\Models\Central\CategoryType;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Foundation\Http\FormRequest;

class TenantSiteRequest extends FormRequest
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

        if (isset($data['surface_floor']) && ($data['surface_floor'] === 0 || $data['surface_floor'] === '0'))
            $data['surface_floor'] = null;

        if (isset($data['surface_walls']) && ($data['surface_walls'] === 0 || $data['surface_walls'] === '0'))
            $data['surface_walls'] = null;

        $this->replace($data);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        $siteTypes = LocationType::where('level', 'site')->pluck('id');

        return [
            'need_qr_code' => 'sometimes|boolean',
            'address' => 'nullable|string',
            'locationType' => ['required', Rule::in([...$siteTypes])],
            'surface_floor' => 'nullable|numeric|gt:0|decimal:0,2',
            'floor_material_id' => ['nullable', Rule::anyOf([Rule::in(CategoryType::where('category', 'floor_materials')->pluck('id')->toArray()), Rule::in('other')])],
            'floor_material_other' => ['nullable', 'required_if:floor_material_id,other'],
            'surface_walls' => 'nullable|numeric|gt:0|decimal:0,2',
            'wall_material_id' => ['nullable', Rule::anyOf([Rule::in(CategoryType::where('category', 'wall_materials')->pluck('id')->toArray()), Rule::in('other')])],
            'wall_material_other' => ['nullable', 'required_if:wall_material_id,other'],
        ];
    }
}
