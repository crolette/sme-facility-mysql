<?php

namespace App\Http\Requests\Tenant;

use App\Enums\RoleTypes;
use Illuminate\Support\Str;
use App\Models\Tenants\User;
use Illuminate\Validation\Rule;
use App\Rules\NotDisposableEmail;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $isUpdate = $this->isMethod('patch');

        if ($isUpdate) {
            // $user = $this->route('user');
            if ($this->user() == $this->route('user'))
                return false;
        }

        return true;
    }

    public function prepareForValidation()
    {
        $data = $this->all();

        if (isset($data['email'])) {
            $data['email'] = Str::lower($data['email']);
        }

        if (isset($data['can_login']) && ($data['can_login'] === "true" || $data['can_login'] === true)) {
            $data['can_login'] = true;
        } else {
            $data['can_login'] = false;
            $data['role'] = null;
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
        $roles = array_column(RoleTypes::cases(), 'value');

        return [
            'email' => ['required', 'string', 'lowercase', 'email', 'min:10', 'max:255', new NotDisposableEmail, Rule::unique(User::class)->ignore($this->route('user'))],
            'first_name' => ['required', 'string', 'min:3', 'max:100'],
            'last_name' => ['required', 'string',  'min:3', 'max:100'],
            'avatar' => 'nullable|file|mimes:png,jpg,jpeg|max:' . User::maxUploadSizeKB(),
            'job_position' => 'nullable|string|max:100',
            'can_login' => 'nullable|boolean',
            'role' => ['nullable', 'string', 'required_if_accepted:can_login', Rule::in($roles)],
            'provider_id' => 'nullable|integer|exists:providers,id',
            'phone_number' => 'nullable|string|regex:/^\+\d{8,15}$/|max:16',
        ];
    }
}
