<?php

namespace App\Actions;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use App\Models\User;
use App\Notifications\AppointmentCancelledNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class UpdateAppointmentStatusAction
{
    public function execute(Appointment $appointment, AppointmentStatus $nextStatus, ?User $changedBy = null, ?string $reason = null): Appointment
    {
        $currentStatus = $appointment->status ?? AppointmentStatus::Scheduled;

        if ($currentStatus === $nextStatus) {
            return $appointment;
        }

        if (! $this->canTransition($currentStatus, $nextStatus)) {
            throw ValidationException::withMessages([
                'status' => 'Transicao de status nao permitida.',
            ]);
        }

        DB::transaction(function () use ($appointment, $changedBy, $currentStatus, $nextStatus, $reason) {
            $updates = [
                'status' => $nextStatus->value,
            ];

            if ($nextStatus === AppointmentStatus::Confirmed && ! $appointment->confirmed_at) {
                $updates['confirmed_at'] = now();
            }

            if ($nextStatus === AppointmentStatus::Cancelled) {
                $updates['cancelled_at'] = now();
            }

            $appointment->update($updates);

            if ($nextStatus === AppointmentStatus::Done) {
                $appointment->client?->update([
                    'last_visit_at' => now(),
                ]);
            }

            AppointmentStatusHistory::query()->create([
                'company_id' => $appointment->company_id,
                'appointment_id' => $appointment->id,
                'from_status' => $currentStatus->value,
                'to_status' => $nextStatus->value,
                'changed_by_user_id' => $changedBy?->id,
                'reason' => $reason,
                'created_at' => now(),
            ]);
        });

        $appointment->refresh();

        if ($nextStatus === AppointmentStatus::Cancelled && $appointment->client?->email) {
            Notification::route('mail', $appointment->client->email)
                ->notify(new AppointmentCancelledNotification($appointment->id));
        }

        return $appointment->load(['client', 'service', 'company.settings']);
    }

    private function canTransition(AppointmentStatus $currentStatus, AppointmentStatus $nextStatus): bool
    {
        $allowedTransitions = [
            AppointmentStatus::Scheduled->value => [
                AppointmentStatus::Confirmed,
                AppointmentStatus::Done,
                AppointmentStatus::NoShow,
                AppointmentStatus::Cancelled,
            ],
            AppointmentStatus::Confirmed->value => [
                AppointmentStatus::Done,
                AppointmentStatus::NoShow,
                AppointmentStatus::Cancelled,
            ],
            AppointmentStatus::Done->value => [],
            AppointmentStatus::NoShow->value => [],
            AppointmentStatus::Cancelled->value => [],
        ];

        return in_array($nextStatus, $allowedTransitions[$currentStatus->value] ?? [], true);
    }
}
