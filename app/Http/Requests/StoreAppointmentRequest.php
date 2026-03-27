<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageAppointments() ?? false;
    }

    public function rules(): array
    {
        return [
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'client_name' => ['required_without:client_id', 'nullable', 'string', 'max:120'],
            'client_phone' => ['required_without:client_id', 'nullable', 'string', 'max:30'],
            'client_email' => ['nullable', 'email', 'max:255'],
            'starts_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
