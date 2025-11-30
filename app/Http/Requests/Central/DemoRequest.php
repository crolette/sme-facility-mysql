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

class DemoRequest extends FormRequest
{

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
            'honey' => 'prohibited', //honeypot
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', new NotDisposableEmail],
            'company' => 'required|string|max:100',
            'phone_number' => 'nullable|string|regex:/^\+\d{8,15}$/|max:16',
            'message' => 'required|string|min:50|max:500',
            'consent' => 'required|accepted',
            'subject' => ['required', 'in:appointment'],
            // 'g-recaptcha-response' => 'required|captcha'
        ];
    }
}
