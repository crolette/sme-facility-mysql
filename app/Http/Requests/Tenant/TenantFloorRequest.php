<?php

namespace App\Http\Requests\Tenant;

use App\Models\LocationType;
use Illuminate\Validation\Rule;
use App\Models\Tenants\Building;
use Illuminate\Foundation\Http\FormRequest;

class TenantFloorRequest extends FormRequest
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
        $buildings = Building::all()->pluck('id');
        $locationTypes = LocationType::where('level', 'floor')->pluck('id');

        return [
            'levelType' => ['required', 'integer', Rule::in([...$buildings])],
            'locationType' => ['required', 'integer', Rule::in([...$locationTypes])],
            'surface_floor' => 'nullable|numeric|gt:0|decimal:0,2',
            'surface_walls' => 'nullable|numeric|gt:0|decimal:0,2',
        ];
    }
}
