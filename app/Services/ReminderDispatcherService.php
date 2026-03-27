<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\ReminderType;
use App\Jobs\SendAppointmentReminderJob;
use App\Models\Appointment;
use App\Models\AppointmentReminder;

class ReminderDispatcherService
{
    /**
     * @return array{24h: int, 2h: int}
     */
    public function dispatch(): array
    {
        $result = [
            ReminderType::OneDay->value => 0,
            ReminderType::TwoHours->value => 0,
        ];

        foreach (ReminderType::cases() as $type) {
            $target = now()->addMinutes($type->minutesBefore());
            $windowStart = $target->copy()->subMinutes(5);
            $windowEnd = $target->copy()->addMinutes(5);

            $appointments = Appointment::query()
                ->with(['company.settings'])
                ->whereBetween('starts_at', [$windowStart, $windowEnd])
                ->whereIn('status', [
                    AppointmentStatus::Scheduled->value,
                    AppointmentStatus::Confirmed->value,
                ])
                ->get();

            foreach ($appointments as $appointment) {
                $settings = $appointment->company?->settings;

                if ($type === ReminderType::OneDay && ! ($settings?->reminder_24h_enabled ?? true)) {
                    continue;
                }

                if ($type === ReminderType::TwoHours && ! ($settings?->reminder_2h_enabled ?? true)) {
                    continue;
                }

                $reminder = AppointmentReminder::query()->firstOrCreate(
                    [
                        'appointment_id' => $appointment->id,
                        'reminder_type' => $type->value,
                    ],
                    [
                        'company_id' => $appointment->company_id,
                        'scheduled_for' => $target,
                        'channel' => 'mail',
                    ],
                );

                if ($reminder->wasRecentlyCreated) {
                    SendAppointmentReminderJob::dispatch($appointment->id, $type->value);
                    $result[$type->value]++;
                }
            }
        }

        return $result;
    }
}
