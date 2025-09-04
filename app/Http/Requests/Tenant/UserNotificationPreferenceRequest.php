<?php

namespace App\Http\Requests\Tenant;

use App\Enums\RoleTypes;
use Illuminate\Support\Str;
use App\Models\Tenants\User;
use Illuminate\Validation\Rule;
use App\Rules\NotDisposableEmail;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Foundation\Http\FormRequest;

class UserNotificationPreferenceRequest extends FormRequest
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
            'asset_type' => ['required', Rule::in(collect(config('notifications.notification_types'))->keys())],
            'notification_type' => [
                'required',
                Rule::in(config("notifications.notification_types.{$this->asset_type}", []))
            ],
            'notification_delay_days' => 'nullable|integer|min:1',
            'enabled' => 'required|boolean'
        ];
    }
}
