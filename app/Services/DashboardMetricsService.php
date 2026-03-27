<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Company;
use Carbon\CarbonImmutable;

class DashboardMetricsService
{
    /**
     * @return array<string, mixed>
     */
    public function build(Company $company): array
    {
        $timezone = $company->settings?->timezone ?? $company->timezone ?? config('app.timezone');

        $todayStart = CarbonImmutable::now($timezone)->startOfDay()->utc();
        $todayEnd = CarbonImmutable::now($timezone)->endOfDay()->utc();

        $monthStart = CarbonImmutable::now($timezone)->startOfMonth()->utc();
        $monthEnd = CarbonImmutable::now($timezone)->endOfMonth()->utc();

        $baseQuery = Appointment::query()->forCompany($company->id);

        $todayAppointments = (clone $baseQuery)
            ->whereBetween('starts_at', [$todayStart, $todayEnd])
            ->count();

        $upcomingAppointments = Appointment::query()
            ->forCompany($company->id)
            ->with(['service:id,name', 'client:id,name,phone'])
            ->activeBookings()
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->limit(15)
            ->get();

        $cancelledCount = (clone $baseQuery)
            ->where('status', AppointmentStatus::Cancelled->value)
            ->whereBetween('starts_at', [$monthStart, $monthEnd])
            ->count();

        $noShowCount = (clone $baseQuery)
            ->where('status', AppointmentStatus::NoShow->value)
            ->whereBetween('starts_at', [$monthStart, $monthEnd])
            ->count();

        $doneCount = (clone $baseQuery)
            ->where('status', AppointmentStatus::Done->value)
            ->whereBetween('starts_at', [$monthStart, $monthEnd])
            ->count();

        $attendanceBase = $doneCount + $noShowCount + $cancelledCount;
        $attendanceRate = $attendanceBase > 0
            ? round(($doneCount / $attendanceBase) * 100, 1)
            : 0.0;

        return [
            'cards' => [
                'todayAppointments' => $todayAppointments,
                'upcomingAppointments' => $upcomingAppointments->count(),
                'cancelledThisMonth' => $cancelledCount,
                'noShowThisMonth' => $noShowCount,
                'attendanceRate' => $attendanceRate,
                'clients' => $company->clients()->count(),
            ],
            'upcomingAppointments' => $upcomingAppointments->map(function (Appointment $appointment) use ($timezone): array {
                return [
                    'id' => $appointment->id,
                    'starts_at' => $appointment->starts_at->setTimezone($timezone)->toIso8601String(),
                    'starts_at_label' => $appointment->starts_at->setTimezone($timezone)->format('d/m H:i'),
                    'status' => $appointment->status?->value,
                    'service' => $appointment->service?->name,
                    'client' => $appointment->client?->name,
                    'phone' => $appointment->client?->phone,
                ];
            })->values()->all(),
        ];
    }
}
