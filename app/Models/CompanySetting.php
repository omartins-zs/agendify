<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    use BelongsToCompany;
    use HasFactory;

    protected $fillable = [
        'company_id',
        'auto_confirm_bookings',
        'allow_client_cancellation',
        'cancellation_window_hours',
        'booking_confirmation_ttl_minutes',
        'reminder_24h_enabled',
        'reminder_2h_enabled',
        'timezone',
    ];

    protected function casts(): array
    {
        return [
            'auto_confirm_bookings' => 'boolean',
            'allow_client_cancellation' => 'boolean',
            'reminder_24h_enabled' => 'boolean',
            'reminder_2h_enabled' => 'boolean',
            'cancellation_window_hours' => 'integer',
            'booking_confirmation_ttl_minutes' => 'integer',
        ];
    }
}
