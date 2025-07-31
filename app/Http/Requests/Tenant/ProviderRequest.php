<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\Tenants\Provider;
use App\Rules\NotDisposableEmail;
use Illuminate\Foundation\Http\FormRequest;

class ProviderRequest extends FormRequest
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

        if (isset($data['email'])) {
            $data['email'] = Str::lower($data['email']);
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
        return [
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', new NotDisposableEmail, Rule::unique(Provider::class)->ignore($this->route('provider'))],
            'name' => ['required', 'string', 'max:255'],
            'address' => 'nullable|string',
            'vat_number' => ['nullable', 'string', 'regex:/^[A-Z]{2}[0-9A-Z]{2,12}$/', 'max:14', Rule::unique(Provider::class)->ignore($this->route('provider'))],
            'phone_number' => 'nullable|string',
            'logo' => 'nullable|file|mimes:png,jpg,jpeg|max:' . Provider::maxUploadSizeKB(),
        ];
    }
}
