<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\Tenants\User;
use App\Rules\NotDisposableEmail;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', new NotDisposableEmail, Rule::unique(User::class)->ignore($this->route('user'))],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'avatar' => 'nullable|file|mimes:png,jpg,jpeg|max:' . User::maxUploadSizeKB(),
        ];
    }
}
