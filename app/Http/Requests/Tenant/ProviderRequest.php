<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Http\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\Tenants\Provider;
use App\Rules\NotDisposableEmail;
use App\Models\Central\CategoryType;
use Barryvdh\Debugbar\Facades\Debugbar;
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
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', new NotDisposableEmail, Rule::unique(Provider::class)->ignore($this->route('provider'))],
            'name' => ['required', 'string', 'max:255'],

            'street' => 'required|string|max:100',
            'house_number' => 'nullable|string|max:10',
            'postal_code' => 'required|string|max:8',
            'city' => 'required|string|max:100',
            'country_code' => ['required', 'string', 'exists:countries,iso_code'],

            'vat_number' => ['nullable', 'string', 'regex:/^[A-Z]{2}[0-9A-Z]{2,12}$/', 'max:14', Rule::unique(Provider::class)->ignore($this->route('provider'))],
            'phone_number' => 'required|string|regex:/^\+\d{8,15}$/|max:16',
            'website' => 'nullable|url:http,https',
            'logo' => 'nullable|file|mimes:png,jpg,jpeg|max:' . Provider::maxUploadSizeKB(),
            'categories' => 'array|min:1',
            // 'categories.*.id' => ['required', Rule::in(CategoryType::where('category', 'provider')->pluck('id')->toArray())]

        ];
    }
}
