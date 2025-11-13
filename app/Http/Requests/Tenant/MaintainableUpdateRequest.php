<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Validation\Rule;
use App\Enums\MaintenanceFrequency;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Foundation\Http\FormRequest;

class MaintainableUpdateRequest extends FormRequest
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

        isset($data['need_maintenance']) && ($data['need_maintenance'] === 'true' || $data['need_maintenance'] === true) ? $data['need_maintenance'] = true : $data['need_maintenance'] = false;
        isset($data['under_warranty']) && ($data['under_warranty'] === 'true' || $data['under_warranty'] === true) ? $data['under_warranty'] = true : $data['under_warranty'] = false;

        if (isset($data['need_maintenance']) && $data['need_maintenance'] === true && !isset($data['next_maintenance_date'])) {
            if (isset($data['maintenance_frequency']) && $data['maintenance_frequency'] !== MaintenanceFrequency::ONDEMAND->value) {
                $data['next_maintenance_date'] = isset($data['last_maintenance_date']) ? calculateNextMaintenanceDate($data['maintenance_frequency'], $data['last_maintenance_date']) : calculateNextMaintenanceDate($data['maintenance_frequency']);
            }
        }

        if ($data['need_maintenance'] === false) {
            $data['next_maintenance_date'] = null;
            $data['last_maintenance_date'] = null;
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

        $frequencies = array_column(MaintenanceFrequency::cases(), 'value');

        return [
            'name' => 'required|string|min:4|max:100',
            'description' => 'nullable|string|min:10|max:255',
            'purchase_date' => ['nullable', 'date', Rule::date()->todayOrBefore()],
            'purchase_cost' => 'nullable|numeric|gt:0|decimal:0,2',
            'under_warranty' => "boolean",
            'end_warranty_date' => ['nullable', 'date', 'required_if_accepted:under_warranty',  Rule::when($this->input('under_warranty') === true, 'after:today'),   Rule::when($this->filled('purchase_date'), 'after:purchase_date')],
            'providers' => 'nullable|array',
            'providers.*.id' => 'integer|exists:providers,id',
            'maintenance_manager_id' => 'nullable|exists:users,id',
            'need_maintenance' => "boolean",
            'maintenance_frequency' => ['nullable', 'required_if_accepted:need_maintenance', Rule::in($frequencies)],
            'next_maintenance_date' => ['nullable', 'date'],
            'last_maintenance_date' =>  ['nullable', 'date', Rule::date()->todayOrBefore()],
        ];
    }
}
