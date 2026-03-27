<?php

use App\Enums\AppointmentStatus;
use App\Services\DashboardMetricsService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

afterEach(function () {
    Carbon::setTestNow();
});

it('builds dashboard metrics with monthly totals and attendance rate', function () {
    Carbon::setTestNow(CarbonImmutable::parse('2026-04-10 12:00:00', 'UTC'));

    $company = makeCompany(settings: ['timezone' => 'America/Sao_Paulo']);
    $otherCompany = makeCompany();

    $service = makeService($company, ['name' => 'Consulta']);
    $client = makeClient($company, ['name' => 'Alice']);
    $secondClient = makeClient($company, ['name' => 'Bob']);

    // Today
    makeAppointment($company, $service, $client, [
        'starts_at' => CarbonImmutable::parse('2026-04-10 13:00:00', 'UTC'),
        'ends_at' => CarbonImmutable::parse('2026-04-10 13:30:00', 'UTC'),
        'status' => AppointmentStatus::Scheduled->value,
    ]);

    // Monthly status counters
    makeAppointment($company, $service, $client, [
        'starts_at' => CarbonImmutable::parse('2026-04-05 13:00:00', 'UTC'),
        'ends_at' => CarbonImmutable::parse('2026-04-05 13:30:00', 'UTC'),
        'status' => AppointmentStatus::Done->value,
    ]);
    makeAppointment($company, $service, $client, [
        'starts_at' => CarbonImmutable::parse('2026-04-06 13:00:00', 'UTC'),
        'ends_at' => CarbonImmutable::parse('2026-04-06 13:30:00', 'UTC'),
        'status' => AppointmentStatus::NoShow->value,
    ]);
    makeAppointment($company, $service, $client, [
        'starts_at' => CarbonImmutable::parse('2026-04-07 13:00:00', 'UTC'),
        'ends_at' => CarbonImmutable::parse('2026-04-07 13:30:00', 'UTC'),
        'status' => AppointmentStatus::Cancelled->value,
    ]);

    // Upcoming
    makeAppointment($company, $service, $secondClient, [
        'starts_at' => CarbonImmutable::parse('2026-04-10 14:00:00', 'UTC'),
        'ends_at' => CarbonImmutable::parse('2026-04-10 14:30:00', 'UTC'),
        'status' => AppointmentStatus::Scheduled->value,
    ]);
    makeAppointment($company, $service, $secondClient, [
        'starts_at' => CarbonImmutable::parse('2026-04-11 14:00:00', 'UTC'),
        'ends_at' => CarbonImmutable::parse('2026-04-11 14:30:00', 'UTC'),
        'status' => AppointmentStatus::Confirmed->value,
    ]);
    makeAppointment($company, $service, $secondClient, [
        'starts_at' => CarbonImmutable::parse('2026-04-11 15:00:00', 'UTC'),
        'ends_at' => CarbonImmutable::parse('2026-04-11 15:30:00', 'UTC'),
        'status' => AppointmentStatus::Cancelled->value,
    ]);

    // Must not affect company metrics
    makeAppointment($otherCompany, makeService($otherCompany), makeClient($otherCompany), [
        'starts_at' => CarbonImmutable::parse('2026-04-10 14:00:00', 'UTC'),
        'ends_at' => CarbonImmutable::parse('2026-04-10 14:30:00', 'UTC'),
        'status' => AppointmentStatus::Done->value,
    ]);

    $metrics = app(DashboardMetricsService::class)->build($company->fresh()->load('settings'));

    expect($metrics['cards']['todayAppointments'])->toBe(2)
        ->and($metrics['cards']['upcomingAppointments'])->toBe(3)
        ->and($metrics['cards']['cancelledThisMonth'])->toBe(2)
        ->and($metrics['cards']['noShowThisMonth'])->toBe(1)
        ->and($metrics['cards']['attendanceRate'])->toBe(25.0)
        ->and($metrics['cards']['clients'])->toBe(2)
        ->and($metrics['upcomingAppointments'])->toHaveCount(3);
});
