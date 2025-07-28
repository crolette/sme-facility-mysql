<?php

namespace App\Http\Requests\Tenant;

use App\Models\LocationType;
use App\Models\Tenants\Site;
use Illuminate\Validation\Rule;
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
            'levelType' => ['required', Rule::in([...$siteTypes])],
            'locationType' => ['required', Rule::in([...$locationTypes])],
            'surface_floor' => 'nullable|numeric|gt:0|decimal:0,2',
            'surface_walls' => 'nullable|numeric|gt:0|decimal:0,2',
        ];
    }
}
