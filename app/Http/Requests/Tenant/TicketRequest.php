<?php

namespace App\Http\Requests\Tenant;

use App\Enums\TicketStatus;
use Illuminate\Support\Str;
use App\Models\Tenants\User;
use Illuminate\Validation\Rule;
use App\Rules\NotDisposableEmail;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Foundation\Http\FormRequest;

class TicketRequest extends FormRequest
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

        if (isset($data['reporter_email'])) {
            $data['reporter_email'] = Str::lower($data['reporter_email']);
        }

        if (isset($data['being_notified']) && $data['being_notified'] === 'true') {
            $data['being_notified'] = true;
        } else {
            $data['being_notified'] = false;
        }

        if (!isset($data['status'])) {
            $data['status'] = TicketStatus::OPEN->value;
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
        $statuses = array_column(TicketStatus::cases(), 'value');

        $data = $this->all();

        dump($data);
        Debugbar::info($data);

        return [
            'ticket_id' => ['nullable', 'integer', Rule::exists('tickets', 'id')],
            'location_type' => ['nullable', 'string', Rule::in(['sites', 'buildings', 'floors', 'rooms', 'assets'])],
            'location_id' => ['nullable', 'integer'],
            'status' => ['required', 'string', Rule::in(...$statuses)],
            'description' => ['required', 'string', 'min:10'],
            'being_notified' => ['required', 'boolean'],
            'reported_by' => ['nullable', 'required_without:reporter_email', 'exists:App\Models\Tenants\User,id'],
            'reporter_email' => ['nullable', 'required_without:reported_by', 'string', 'email', 'max:255', new NotDisposableEmail],
        ];
    }


    public function withValidator($validator)
    {
        $validator->sometimes('location_id', [
            'exists:' . $this->input('location_type') . ',id'
        ], function () {
            return in_array($this->input('location_type'), ['sites', 'buildings', 'floors', 'rooms', 'assets']);
        });
    }
}
