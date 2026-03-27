<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Company;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class AvailabilityService
{
    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function generateSlots(Company $company, Service $service, CarbonInterface|string $date): array
    {
        $timezone = $company->settings?->timezone ?? $company->timezone ?? config('app.timezone');
        $day = $date instanceof CarbonInterface
            ? CarbonImmutable::instance($date)->setTimezone($timezone)->startOfDay()
            : CarbonImmutable::parse($date, $timezone)->startOfDay();

        $rules = $company->availabilityRules()
            ->where('weekday', $day->dayOfWeek)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();

        if ($rules->isEmpty()) {
            return [];
        }

        $dayStartUtc = $day->utc();
        $dayEndUtc = $day->endOfDay()->utc();

        $appointments = Appointment::query()
            ->forCompany($company->id)
            ->activeBookings()
            ->where('starts_at', '<', $dayEndUtc)
            ->where('ends_at', '>', $dayStartUtc)
            ->get(['starts_at', 'ends_at']);

        $blockedTimes = $company->blockedTimes()
            ->where('starts_at', '<', $dayEndUtc)
            ->where('ends_at', '>', $dayStartUtc)
            ->get(['starts_at', 'ends_at']);

        $duration = max((int) $service->duration_minutes, 5);
        $slots = [];

        foreach ($rules as $rule) {
            $cursor = CarbonImmutable::parse($day->format('Y-m-d').' '.$rule->start_time, $timezone);
            $endBoundary = CarbonImmutable::parse($day->format('Y-m-d').' '.$rule->end_time, $timezone);
            $interval = max((int) $rule->slot_interval_minutes, 5);

            while ($cursor->addMinutes($duration)->lte($endBoundary)) {
                $slotStartUtc = $cursor->utc();
                $slotEndUtc = $cursor->addMinutes($duration)->utc();

                if (! $this->overlaps($slotStartUtc, $slotEndUtc, $appointments) && ! $this->overlaps($slotStartUtc, $slotEndUtc, $blockedTimes)) {
                    $slots[] = [
                        'value' => $cursor->toIso8601String(),
                        'label' => $cursor->format('H:i'),
                    ];
                }

                $cursor = $cursor->addMinutes($interval);
            }
        }

        return $slots;
    }

    public function hasConflict(Company $company, CarbonInterface $startsAtUtc, CarbonInterface $endsAtUtc, ?int $ignoreAppointmentId = null): bool
    {
        return Appointment::query()
            ->forCompany($company->id)
            ->activeBookings()
            ->when($ignoreAppointmentId, fn ($query) => $query->whereKeyNot($ignoreAppointmentId))
            ->where('starts_at', '<', $endsAtUtc)
            ->where('ends_at', '>', $startsAtUtc)
            ->exists();
    }

    private function overlaps(CarbonImmutable $startsAtUtc, CarbonImmutable $endsAtUtc, Collection $ranges): bool
    {
        foreach ($ranges as $range) {
            $rangeStart = CarbonImmutable::parse((string) $range->starts_at);
            $rangeEnd = CarbonImmutable::parse((string) $range->ends_at);

            if ($rangeStart->lt($endsAtUtc) && $rangeEnd->gt($startsAtUtc)) {
                return true;
            }
        }

        return false;
    }
}
