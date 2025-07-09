<?php

namespace App\Http\Requests\Central;

use App\Enums\LevelTypes;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\LocationType;

class LocationTypeRequest extends FormRequest
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
        $this->merge(['prefix' => strtoupper($this->prefix)]);

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
        $type = $this->route('locationType');
        $types = array_map(fn($case) => "{$case->value}", LevelTypes::cases());



        return [
            'prefix' => ['required', 'string', 'min:1', 'max:2',  Rule::unique('location_types')->where(fn($query) => $query->where('level', $this->input('level')))->ignore($type)],
            // 'prefix' => ['required', 'string', 'min:1', 'max:2', Rule::unique(LocationType::class, 'prefix')->ignore($type)],
            'level' => ['required', 'string', Rule::in([...$types])],
            'translations.en' => 'required|string|max:20',
            'translations.*' => 'nullable|string|max:20'
        ];
    }
}
