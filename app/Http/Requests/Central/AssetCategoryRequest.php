<?php

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;

class AssetCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        // convert translation to have uppercase on first letter
        $formattedTranslations = [];

        foreach ($this->translations as $locale => $label) {
            $formattedTranslations[$locale] = ucfirst($label);
        }

        $this->merge(['translations' => $formattedTranslations]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'translations.en' => 'required|string|min:2|max:30',
            'translations.*' => 'nullable|string|min:2|max:30'
        ];
    }
}
