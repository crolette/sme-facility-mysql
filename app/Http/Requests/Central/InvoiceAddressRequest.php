<?php

namespace App\Http\Requests\Central;

use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Rules\NotDisposableEmail;
use Illuminate\Foundation\Http\FormRequest;

class InvoiceAddressRequest extends FormRequest
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
            'same_address_as_company' => 'boolean',
            'invoice.street' => 'nullable|string|max:100',
            'invoice.house_number' => 'nullable|string|max:10',
            'invoice.city' => 'nullable|string|max:50',
            'invoice.zip_code' => 'nullable|string|min:4|max:6',
            'invoice.country' => 'nullable|string|max:30',
        ];
    }
}
