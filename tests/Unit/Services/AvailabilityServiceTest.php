<?php

use App\Enums\AppointmentStatus;
use App\Models\AvailabilityRule;
use App\Models\BlockedTime;
use App\Services\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('generates slots excluding occupied and blocked ranges', function () {
    $company = makeCompany(settings: ['timezone' => 'America/Sao_Paulo']);
    $service = makeService($company, ['duration_minutes' => 30]);

    $date = CarbonImmutable::parse('2026-03-30', 'America/Sao_Paulo'); // Monday

    AvailabilityRule::query()->create([
        'company_id' => $company->id,
        'weekday' => $date->dayOfWeek,
        'start_time' => '09:00:00',
        'end_time' => '11:00:00',
        'slot_interval_minutes' => 30,
        'is_active' => true,
    ]);

    $client = makeClient($company);

    makeAppointment($company, $service, $client, [
        'starts_at' => $date->setTime(9, 30)->utc(),
        'ends_at' => $date->setTime(10, 0)->utc(),
        'status' => AppointmentStatus::Confirmed->value,
    ]);

    BlockedTime::query()->create([
        'company_id' => $company->id,
        'starts_at' => $date->setTime(10, 30)->utc(),
        'ends_at' => $date->setTime(11, 0)->utc(),
        'reason' => 'Pausa',
    ]);

    $slots = app(AvailabilityService::class)->generateSlots(
        company: $company->fresh()->load('settings'),
        service: $service,
        date: $date->format('Y-m-d'),
    );

    expect(collect($slots)->pluck('label')->all())->toBe(['09:00', '10:00']);
});

it('returns empty slots when there is no active rule for the day', function () {
    $company = makeCompany();
    $service = makeService($company);

    $slots = app(AvailabilityService::class)->generateSlots(
        company: $company,
        service: $service,
        date: '2026-03-30',
    );

    expect($slots)->toBe([]);
});

it('detects conflicts and can ignore a specific appointment id', function () {
    $company = makeCompany();
    $service = makeService($company, ['duration_minutes' => 60]);
    $client = makeClient($company);

    $appointment = makeAppointment($company, $service, $client, [
        'starts_at' => CarbonImmutable::parse('2026-03-30 13:00:00', 'UTC'),
        'ends_at' => CarbonImmutable::parse('2026-03-30 14:00:00', 'UTC'),
        'status' => AppointmentStatus::Scheduled->value,
    ]);

    $hasConflict = app(AvailabilityService::class)->hasConflict(
        company: $company,
        startsAtUtc: CarbonImmutable::parse('2026-03-30 13:30:00', 'UTC'),
        endsAtUtc: CarbonImmutable::parse('2026-03-30 13:45:00', 'UTC'),
    );

    $ignoringCurrent = app(AvailabilityService::class)->hasConflict(
        company: $company,
        startsAtUtc: CarbonImmutable::parse('2026-03-30 13:30:00', 'UTC'),
        endsAtUtc: CarbonImmutable::parse('2026-03-30 13:45:00', 'UTC'),
        ignoreAppointmentId: $appointment->id,
    );

    expect($hasConflict)->toBeTrue()
        ->and($ignoringCurrent)->toBeFalse();
});
