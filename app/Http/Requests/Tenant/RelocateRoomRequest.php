<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Support\Str;
use App\Models\LocationType;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class RelocateRoomRequest extends FormRequest
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
        $locationTypes = LocationType::where('level', 'room')->pluck('id');

        // TODO location ID needs to be checked depending on the location Type

        return [
            'locationType' => ['required', Rule::in([...$locationTypes])],
            'assets.*' => 'nullable|array',
            'assets.*.change' => 'nullable|in:follow,relocate,delete',
            'assets.*.assetId' => 'nullable|exists:assets,id',
            'assets.*.locationType' => 'nullable|in:room',
            'assets.*.locationId' => 'nullable|exists:rooms,id'
        ];
    }
}
