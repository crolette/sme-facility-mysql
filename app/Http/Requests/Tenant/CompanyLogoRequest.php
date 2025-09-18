<?php

namespace App\Http\Requests\Tenant;

use App\Models\Tenants\Company;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\Tenants\Provider;
use App\Rules\NotDisposableEmail;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\File;

class CompanyLogoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }



    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => 'nullable|file|mimes:png,jpg,jpeg|max:' . Company::maxUploadSizeKB(),
        ];
    }
}
