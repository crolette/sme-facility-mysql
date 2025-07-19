<?php

namespace App\Http\Requests\Tenant;

use App\Enums\PriorityLevel;
use Illuminate\Validation\Rule;
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
        return [
            'intervention_type_id' => ['required', Rule::in(CategoryType::where('category', 'intervention')->pluck('id')->toArray())],

            'status' => ['required', 'string', Rule::in(array_column(InterventionStatus::cases(), 'value'))],
            'priority' => ['required', 'string', Rule::in(array_column(PriorityLevel::cases(), 'value'))],

            'planned_at' => ['nullable', 'date', Rule::date()->afterOrEqual(today())],
            'description' => ['nullable', 'string'],
            'repair_delay' => ['nullable', 'date', Rule::date()->afterOrEqual(today())],
            'total_costs' => ['nullable', 'numeric', 'decimal:2,4'],

            'locationType' => ['nullable', 'required_without:ticket_id', 'in:sites,buildings,floors,rooms,asset'],
            'locationId' => ['nullable', 'required_without:ticket_id'],

            'ticket_id' => ['nullable', Rule::exists('tickets', 'id')],


        ];
    }
}
