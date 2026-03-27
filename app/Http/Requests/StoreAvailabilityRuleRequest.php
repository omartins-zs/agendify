<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAvailabilityRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageAvailability() ?? false;
    }

    public function rules(): array
    {
        return [
            'weekday' => ['required', 'integer', 'min:0', 'max:6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'slot_interval_minutes' => ['required', 'integer', 'min:5', 'max:120'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
