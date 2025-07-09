<?php

namespace App\Http\Requests\Tenant;

use App\Models\LocationType;
use Illuminate\Validation\Rule;
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        $siteTypes = LocationType::where('level', 'site')->pluck('id');

        return [
            'locationType' => ['required', Rule::in([...$siteTypes])]
        ];
    }
}
