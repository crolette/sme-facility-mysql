<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Validation\Rule;
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

        // $data = $this->all();

        // if (($data['purchase_cost']) == 0) {
        //     $data['purchase_cost'] = null;
        // }

        // $this->replace($data);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|min:6|max:100',
            'description' => 'nullable|string|min:10|max:255',
            'purchase_date' => ['nullable', 'date', Rule::date()->beforeOrEqual(today())],
            'purchase_cost' => 'nullable|numeric|gt:0|decimal:0,2',
            'under_warranty' => "boolean",
            'end_warranty_date' => "nullable|date|required_if_accepted:under_warranty|after:purchase_date",
            'providers' => 'nullable|array',
            'providers.*' => 'integer|exists:providers,id'
        ];
    }
}
