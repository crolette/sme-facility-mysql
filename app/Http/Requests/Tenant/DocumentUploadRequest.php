<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Validation\Rule;
use App\Models\Tenants\Document;
use App\Models\Central\CategoryType;
use Illuminate\Foundation\Http\FormRequest;

class DocumentUploadRequest extends FormRequest
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
            'files' => 'nullable|array',
            'files.*.name' => 'required_with:files.*.file|string|min:10|max:100',
            'files.*.description' => 'nullable|string|min:10|max:250',
            'files.*.file' => 'required_with:files.*.name|file|mimes:jpg,jpeg,png,pdf|max:' . Document::maxUploadSizeKB(),
            'files.*.typeId' => ['required_with:files.*.name', Rule::in(CategoryType::where('category', 'document')->pluck('id')->toArray())],
            'files.*.typeSlug' => ['required_with:files.*.name', Rule::in(CategoryType::where('category', 'document')->pluck('slug')->toArray())],

            'existing_documents' => 'nullable|array',
            'existing_documents.*' => 'required|exists:documents,id',
        ];
    }
}
