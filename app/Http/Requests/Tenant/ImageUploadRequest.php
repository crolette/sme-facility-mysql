<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Http\File;
use Illuminate\Support\Str;
use App\Models\Tenants\Picture;
use Illuminate\Validation\Rule;
use App\Models\Tenants\Provider;
use App\Rules\NotDisposableEmail;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Foundation\Http\FormRequest;

class ImageUploadRequest extends FormRequest
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
            'pictures' => 'nullable|array|max:1',
            'pictures.*' => 'image|mimes:jpg,jpeg,png|max:' . Picture::maxUploadSizeKB(),
        ];
    }
}
