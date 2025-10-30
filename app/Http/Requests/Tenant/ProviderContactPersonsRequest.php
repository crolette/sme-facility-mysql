<?php

namespace App\Http\Requests\Tenant;

use App\Enums\RoleTypes;
use Illuminate\Support\Str;
use App\Models\Tenants\User;
use Illuminate\Validation\Rule;
use App\Rules\NotDisposableEmail;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Foundation\Http\FormRequest;

class ProviderContactPersonsRequest extends FormRequest
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

        if (isset($data['users'])) {
            foreach ($data['users'] as $user) {
                if (isset($user['email'])) {
                    $user['email'] = Str::lower($user['email']);
                }
            }
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
            'users.*.email' => ['required', 'string', 'lowercase', 'email', 'max:255', new NotDisposableEmail, Rule::unique(User::class)->ignore($this->route('user'))],
            'users.*.first_name' => ['required', 'string', 'min:3', 'max:255'],
            'users.*.last_name' => ['required', 'string',  'min:3', 'max:255'],
            'users.*.phone_number' => 'required|string|regex:/^\+\d{8,15}$/|max:16',
            'users.*.job_position' => 'nullable|string|max:100',
        ];
    }
}
