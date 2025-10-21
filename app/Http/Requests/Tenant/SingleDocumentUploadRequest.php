<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Validation\Rule;
use App\Models\Tenants\Document;
use App\Models\Central\CategoryType;
use Illuminate\Foundation\Http\FormRequest;

class SingleDocumentUploadRequest extends FormRequest
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
            'name' => 'required_with:file|string|min:10|max:100',
            'description' => 'nullable|string|min:10|max:250',
            'file' => 'required_with:name|file|mimes:jpg,jpeg,png,pdf|max:' . Document::maxUploadSizeKB(),
            'typeId' => ['required_with:name', Rule::in(CategoryType::where('category', 'document')->pluck('id')->toArray())],
            // 'typeSlug' => ['required_with:name', Rule::in(CategoryType::where('category', 'document')->pluck('slug')->toArray())],

        ];
    }
}
