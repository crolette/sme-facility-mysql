<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StatisticsRequest extends FormRequest
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

        if (!isset($data['period']))
            $data['period'] = 'week';

        if (!isset($data['date_from']))
            $data['date_from'] = '2025-01-01';

        if (!isset($data['date_to']))
            $data['date_to'] = '2025-12-31';

        $this->replace($data);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'period' => 'nullable|string|in:week,month',
            'date_from' => 'nullable|date:y-m-d',
            'date_to' => 'nullable|date:y-m-d'
        ];
    }
}
