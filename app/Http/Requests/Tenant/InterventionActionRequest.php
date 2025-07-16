<?php

namespace App\Http\Requests○\Tenant;

use App\Enums\PriorityLevel;
use Illuminate\Validation\Rule;
use App\Enums\InterventionStatus;
use App\Rules\NotDisposableEmail;
use Illuminate\Foundation\Http\FormRequest;

class InterventionActionRequest extends FormRequest
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
            'action_type_id' => ['required', Rule::in(CategoryType::where('category', 'intervention')->pluck('id')->toArray())],

            'description' => ['nullable', 'string'],
            'intervention_date' => ['nullable', 'date'],
            'started_at' => ['nullable', 'date_format:H:i:s'],
            'finished_at' => ['nullable', 'date_format:H:i:s', 'after:start_time'],
            'intervention_costs' => ['nullable', 'numeric', 'decimal:2,4'],

            'created_by' => ['nullable', 'required_without:creator_email', 'exists:App\Models\Tenants\User,id'],
            'creator_email' => ['nullable', 'required_without:created_by', 'string', 'email', 'max:255', new NotDisposableEmail],

            'updated_by' => ['nullable', 'exists:App\Models\Tenants\User,id'],

        ];
    }
}
