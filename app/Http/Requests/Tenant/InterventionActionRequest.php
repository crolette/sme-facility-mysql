<?php

namespace App\Http\Requests\Tenant;

use App\Enums\PriorityLevel;
use Illuminate\Validation\Rule;
use App\Enums\InterventionStatus;
use App\Rules\NotDisposableEmail;
use App\Models\Central\CategoryType;
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
        $isUpdate = $this->isMethod('patch');

        $rules = [
            'action_type_id' => ['required', Rule::in(CategoryType::where('category', 'action')->pluck('id')->toArray())],

            'description' => ['nullable', 'string'],
            'intervention_date' => ['nullable', 'date', Rule::date()->beforeOrEqual(today())],
            'started_at' => ['nullable', 'date_format:H:i'],
            'finished_at' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'intervention_costs' => ['nullable', 'numeric', 'decimal:0,2'],

            'created_by' => ['nullable', 'required_without:creator_email', 'exists:App\Models\Tenants\User,id'],
            'creator_email' => ['nullable', 'required_without:created_by', 'string', 'email', 'max:255', new NotDisposableEmail],

            'updated_by' => ['nullable', 'exists:App\Models\Tenants\User,id'],

        ];

        // when update, creator shouldn't be changed
        if ($isUpdate) {
            $rules['created_by'] = ['nullable'];
            $rules['creator_email'] = ['nullable'];
        }


        return $rules;
    }
}
