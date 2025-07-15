<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Validation\Rule;
use App\Models\Tenants\Document;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Picture;
use Illuminate\Foundation\Http\FormRequest;

class PictureUploadRequest extends FormRequest
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
            'pictures' => 'nullable|array',
            'pictures.*' => 'image|mimes:jpg,jpeg,png|max:' . Picture::maxUploadSizeKB(),
        ];
    }
}
