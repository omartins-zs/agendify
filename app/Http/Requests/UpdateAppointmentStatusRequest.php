<?php

namespace App\Http\Requests;

use App\Enums\AppointmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageAppointments() ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in(array_map(
                    static fn (AppointmentStatus $status): string => $status->value,
                    AppointmentStatus::cases(),
                )),
            ],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
