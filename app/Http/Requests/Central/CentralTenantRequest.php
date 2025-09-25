<?php

namespace App\Http\Requests\Central;

use Illuminate\Validation\Rules\Password;
use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Rules\NotDisposableEmail;
use Illuminate\Foundation\Http\FormRequest;

class CentralTenantRequest extends FormRequest
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
            'company_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            // 'password' => ['required', 'confirmed', Password::defaults()],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', new NotDisposableEmail, Rule::unique(Tenant::class)->ignore($this->route('tenant'))],
            'vat_number' => 'required|string|regex:/^[A-Z]{2}[0-9A-Z]{2,12}$/|max:14',
            'domain_name' => ['required', 'string', 'min:3', 'max:12', Rule::unique(Domain::class, 'domain')->ignore(optional($this->route('tenant'))->domain)],
            'phone_number' => 'required|string|regex:/^\+\d{8,15}$/|max:16',
            'company_code' => 'required|string|max:4',
        ];
    }
}
