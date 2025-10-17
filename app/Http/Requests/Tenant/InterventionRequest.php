<?php

namespace App\Http\Requests\Tenant;

use App\Enums\PriorityLevel;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Ticket;
use Illuminate\Validation\Rule;
use App\Models\Tenants\Building;
use App\Enums\InterventionStatus;
use App\Models\Central\CategoryType;
use Illuminate\Foundation\Http\FormRequest;

class InterventionRequest extends FormRequest
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
        $isUpdate = $this->isMethod('patch');


        $rules = [

            'intervention_type_id' => ['required', Rule::in(CategoryType::where('category', 'intervention')->pluck('id')->toArray())],

            'status' => ['required', 'string', Rule::in(array_column(InterventionStatus::cases(), 'value'))],
            'priority' => ['required', 'string', Rule::in(array_column(PriorityLevel::cases(), 'value'))],

            'planned_at' => ['nullable', 'date', Rule::date()->afterOrEqual(today())],
            'description' => ['nullable', 'string'],
            'repair_delay' => ['nullable', 'date', Rule::date()->afterOrEqual(today())],
            'total_costs' => ['nullable', 'numeric', 'decimal:0,2'],
            // 'in:sites,buildings,floors,rooms,asset'
            'locationType' => ['nullable', 'required_without:ticket_id', Rule::in([Site::class, Building::class, Floor::class, Room::class, Asset::class, Ticket::class])],
            'locationId' => ['nullable', 'required_without:ticket_id'],

            'ticket_id' => ['nullable', Rule::exists('tickets', 'id')],
        ];

        if ($isUpdate) {
            $rules['planned_at'] = ['nullable', 'date'];
            $rules['repair_delay'] = ['nullable', 'date'];
        }

        return $rules;
    }
}
