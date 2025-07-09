<?php

namespace App\Http\Requests\Tenant;

use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\Floor;
use Illuminate\Validation\Rule;
use App\Models\Tenants\Building;
use Illuminate\Validation\Validator;
use App\Models\Central\AssetCategory;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class AssetRequest extends FormRequest
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

        $data = $this->all();

        $rules = [
            'locationType' => ['nullable', 'in:site,building,floor,room'],
            'locationId' => ['nullable'],
            'locationReference' => ['nullable'],
            'categoryId' => ['required', Rule::in(AssetCategory::pluck('id')->toArray())],
            'model' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:50'],
        ];

        Debugbar::info($this->input('locationType'));
        $isCreate = $this->isMethod('post');
        $type = $this->input('locationType');


        if ($isCreate && $type === null) {
            throw ValidationException::withMessages([
                'locationType' => __('validation.required', ['attribute' => 'location type'])
            ]);;
        }

        if ($type !== null && !in_array($type, ['site', 'building', 'floor', 'room'], true)) {
            throw ValidationException::withMessages([
                'locationType' => __('validation.in', ['attribute' => 'location type'])
            ]);
        }




        if (in_array($type, ['site', 'building', 'floor', 'room'], true)) {
            $modelMap = [
                'site' => \App\Models\Tenants\Site::class,
                'building' => \App\Models\Tenants\Building::class,
                'floor' => \App\Models\Tenants\Floor::class,
                'room' => \App\Models\Tenants\Room::class,
            ];

            $model = $modelMap[$type];
        }

        // TODO Required_with rule ?

        if ($isCreate) {
            $rules['locationType'] = ['required', 'in:site,building,floor,room'];
            $rules['locationId'] = ['required', Rule::in($model::pluck('id')->toArray())];
            $rules['locationReference'] = ['required', Rule::in($model::pluck('reference_code')->toArray())];
        } else {
            if ($this->filled('locationType') || $this->filled('locationId') || $this->filled('locationReference')) {
                $rules['locationType'] = ['required', 'in:site,building,floor,room'];
                $rules['locationId'] = ['required', Rule::in($model::pluck('id')->toArray())];
                $rules['locationReference'] = ['required', Rule::in($model::pluck('reference_code')->toArray())];
            }
        }

        return $rules;
    }
}
