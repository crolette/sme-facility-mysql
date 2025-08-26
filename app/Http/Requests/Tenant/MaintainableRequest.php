<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Validation\Rule;
use App\Enums\MaintenanceFrequency;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Foundation\Http\FormRequest;

class MaintainableRequest extends FormRequest
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

        if (isset($data['need_maintenance']) && $data['need_maintenance'] === true) {
            if (isset($data['next_maintenance_date'])) {
                return;
            }
            if (isset($data['maintenance_frequency']) && $data['maintenance_frequency'] === MaintenanceFrequency::ONDEMAND->value) {
                return;
            } else {
                $data['next_maintenance_date'] = calculateNextMaintenanceDate($data['maintenance_frequency']);
            }
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
        Debugbar::info($this->input('need_maintenance'));

        return [
            'name' => 'required|string|min:4|max:100',
            'description' => 'nullable|string|min:10|max:255',
            'purchase_date' => ['nullable', 'date', Rule::date()->todayOrBefore()],
            'purchase_cost' => 'nullable|numeric|gt:0|decimal:0,2',
            'under_warranty' => "boolean",
            'end_warranty_date' => "nullable|date|required_if_accepted:under_warranty|after:purchase_date",
            'providers' => 'nullable|array',
            'providers.*.id' => 'integer|exists:providers,id',
            'maintenance_manager_id' => 'nullable|exists:users,id',
            'need_maintenance' => "boolean",
            'maintenance_frequency' => ['nullable', 'required_if_accepted:need_maintenance', Rule::in($frequencies)],
            'next_maintenance_date' => ['nullable', 'date', Rule::date()->todayOrAfter()],
            'last_maintenance_date' =>  ['nullable', 'date', Rule::date()->todayOrBefore()],
        ];
    }
}
