<?php

namespace App\Actions;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use App\Models\Client;
use App\Models\Company;
use App\Models\Service;
use App\Models\User;
use App\Notifications\AppointmentCreatedNotification;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateAppointmentAction
{
    public function execute(
        Company $company,
        Service $service,
        Client $client,
        CarbonInterface|string $startsAt,
        ?User $bookedBy = null,
        string $source = 'public_link',
        ?string $notes = null,
    ): Appointment {
        if ($service->company_id !== $company->id || ! $service->is_active) {
            throw ValidationException::withMessages([
                'service_id' => 'Servico invalido para esta empresa.',
            ]);
        }

        if ($client->company_id !== $company->id) {
            throw ValidationException::withMessages([
                'client_id' => 'Cliente invalido para esta empresa.',
            ]);
        }

        $timezone = $company->settings?->timezone ?? $company->timezone ?? config('app.timezone');
        $startLocal = $startsAt instanceof CarbonInterface
            ? CarbonImmutable::instance($startsAt)->setTimezone($timezone)
            : CarbonImmutable::parse($startsAt, $timezone);

        $startsAtUtc = $startLocal->utc();
        $endsAtUtc = $startsAtUtc->addMinutes(max((int) $service->duration_minutes, 5));

        $appointment = DB::transaction(function () use ($bookedBy, $client, $company, $endsAtUtc, $notes, $service, $source, $startsAtUtc) {
            $hasConflict = Appointment::query()
                ->forCompany($company->id)
                ->activeBookings()
                ->where('starts_at', '<', $endsAtUtc)
                ->where('ends_at', '>', $startsAtUtc)
                ->lockForUpdate()
                ->exists();

            if ($hasConflict) {
                throw ValidationException::withMessages([
                    'starts_at' => 'Este horario acabou de ser ocupado. Escolha outro horario.',
                ]);
            }

            $status = $company->settings?->auto_confirm_bookings
                ? AppointmentStatus::Confirmed
                : AppointmentStatus::Scheduled;

            $appointment = Appointment::query()->create([
                'company_id' => $company->id,
                'service_id' => $service->id,
                'client_id' => $client->id,
                'user_id' => $bookedBy?->id,
                'starts_at' => $startsAtUtc,
                'ends_at' => $endsAtUtc,
                'status' => $status->value,
                'source' => $source,
                'confirmation_token' => Str::random(48),
                'confirmed_at' => $status === AppointmentStatus::Confirmed ? now() : null,
                'notes' => $notes,
            ]);

            AppointmentStatusHistory::query()->create([
                'company_id' => $company->id,
                'appointment_id' => $appointment->id,
                'from_status' => null,
                'to_status' => $status->value,
                'changed_by_user_id' => $bookedBy?->id,
                'reason' => 'Criacao do agendamento',
                'created_at' => now(),
            ]);

            return $appointment;
        });

        if ($client->email) {
            Notification::route('mail', $client->email)
                ->notify(new AppointmentCreatedNotification($appointment->id));
        }

        return $appointment->load(['client', 'service', 'company.settings']);
    }
}
