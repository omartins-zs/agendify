<?php

namespace App\Jobs;

use App\Enums\AppointmentStatus;
use App\Enums\ReminderType;
use App\Models\Appointment;
use App\Models\AppointmentReminder;
use App\Notifications\AppointmentReminderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendAppointmentReminderJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $appointmentId,
        public string $reminderType,
    ) {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        $type = ReminderType::from($this->reminderType);

        $appointment = Appointment::query()
            ->with(['client', 'service', 'company.settings'])
            ->find($this->appointmentId);

        if (! $appointment) {
            return;
        }

        $status = $appointment->status instanceof AppointmentStatus
            ? $appointment->status
            : AppointmentStatus::from((string) $appointment->status);

        if (! $status->isActiveBooking()) {
            return;
        }

        if ($appointment->client?->email) {
            Notification::route('mail', $appointment->client->email)
                ->notify(new AppointmentReminderNotification($appointment->id, $type->value));
        }

        AppointmentReminder::query()
            ->where('appointment_id', $appointment->id)
            ->where('reminder_type', $type->value)
            ->whereNull('dispatched_at')
            ->update(['dispatched_at' => now()]);
    }
}
