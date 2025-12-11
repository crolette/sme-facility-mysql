<?php

namespace App\Http\Requests\Tenant;

use Carbon\Carbon;
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
            $data['date_from'] = Carbon::now()->subYear()->toDateString();

        if (!isset($data['date_to']))
            $data['date_to'] = Carbon::now()->toDateString();;

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
