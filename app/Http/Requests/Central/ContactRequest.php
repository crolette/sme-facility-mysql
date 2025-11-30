<?php

namespace App\Http\Requests\Central;

use Illuminate\Support\Str;
use App\Enums\ContactReasons;
use Illuminate\Validation\Rule;
use App\Rules\NotDisposableEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{

    public function prepareForValidation()
    {
        $data = $this->all();

        if (isset($data['email'])) {
            $data['email'] = Str::lower($data['email']);
        }

        if (isset($data['website'])) {
            $data['website'] = Str::startsWith($data['website'], ['http', 'https']) ? Str::lower($data['website']) : Str::lower('https://' . $data['website']);
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
            'honey' => 'prohibited', //honeypot
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', new NotDisposableEmail],
            'company' => 'required|string|max:100',
            'vat_number' => ['nullable', 'string', 'regex:/^[A-Z]{2}[0-9A-Z]{2,12}$/', 'max:14'],
            'phone_number' => 'required|string|regex:/^\+\d{8,15}$/|max:16',
            'website' => 'nullable|url:http,https',
            'message' => 'required|string|min:50|max:500',
            'consent' => 'required|accepted',
            'subject' => ['required', Rule::in(array_column(ContactReasons::cases(), 'value'))],
            // 'g-recaptcha-response' => 'required|captcha'
        ];
    }
}
