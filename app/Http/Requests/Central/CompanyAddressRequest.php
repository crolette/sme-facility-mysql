<?php

namespace App\Http\Requests\Central;

use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Rules\NotDisposableEmail;
use Illuminate\Foundation\Http\FormRequest;

class CompanyAddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation() {}


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company.street' => 'required|string|max:100',
            'company.house_number' => 'required|string|max:10',
            'company.city' => 'required|string|max:50',
            'company.zip_code' => 'required|string|min:4|max:6',
            'company.country' => 'required|string|max:30',
        ];
    }
}
