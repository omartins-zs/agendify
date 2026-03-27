<?php

namespace App\Models;

use App\Enums\ReminderType;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentReminder extends Model
{
    use BelongsToCompany;
    use HasFactory;

    protected $fillable = [
        'company_id',
        'appointment_id',
        'reminder_type',
        'scheduled_for',
        'dispatched_at',
        'channel',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_for' => 'datetime',
            'dispatched_at' => 'datetime',
            'reminder_type' => ReminderType::class,
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
