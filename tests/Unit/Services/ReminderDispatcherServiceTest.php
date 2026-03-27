<?php

use App\Enums\AppointmentStatus;
use App\Enums\ReminderType;
use App\Jobs\SendAppointmentReminderJob;
use App\Models\AppointmentReminder;
use App\Services\ReminderDispatcherService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

afterEach(function () {
    Carbon::setTestNow();
});

it('dispatches 24h and 2h reminders for active appointments', function () {
    Queue::fake();
    Carbon::setTestNow(CarbonImmutable::parse('2026-03-24 10:00:00', 'UTC'));

    $company = makeCompany(settings: [
        'reminder_24h_enabled' => true,
        'reminder_2h_enabled' => true,
    ]);
    $service = makeService($company);
    $client = makeClient($company);

    makeAppointment($company, $service, $client, [
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDay()->addMinutes(30),
        'status' => AppointmentStatus::Scheduled->value,
    ]);

    makeAppointment($company, $service, $client, [
        'starts_at' => now()->addHours(2),
        'ends_at' => now()->addHours(2)->addMinutes(30),
        'status' => AppointmentStatus::Confirmed->value,
    ]);

    $result = app(ReminderDispatcherService::class)->dispatch();

    expect($result[ReminderType::OneDay->value])->toBe(1)
        ->and($result[ReminderType::TwoHours->value])->toBe(1)
        ->and(AppointmentReminder::query()->count())->toBe(2);

    Queue::assertPushed(SendAppointmentReminderJob::class, 2);
});

it('respects company settings when 24h reminder is disabled', function () {
    Queue::fake();
    Carbon::setTestNow(CarbonImmutable::parse('2026-03-24 10:00:00', 'UTC'));

    $company = makeCompany(settings: [
        'reminder_24h_enabled' => false,
        'reminder_2h_enabled' => true,
    ]);

    $service = makeService($company);
    $client = makeClient($company);

    makeAppointment($company, $service, $client, [
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDay()->addMinutes(30),
        'status' => AppointmentStatus::Scheduled->value,
    ]);

    $result = app(ReminderDispatcherService::class)->dispatch();

    expect($result[ReminderType::OneDay->value])->toBe(0)
        ->and(AppointmentReminder::query()->count())->toBe(0);

    Queue::assertNothingPushed();
});

it('does not enqueue duplicate reminders for the same appointment and type', function () {
    Queue::fake();
    Carbon::setTestNow(CarbonImmutable::parse('2026-03-24 10:00:00', 'UTC'));

    $company = makeCompany();
    $service = makeService($company);
    $client = makeClient($company);

    makeAppointment($company, $service, $client, [
        'starts_at' => now()->addHours(2),
        'ends_at' => now()->addHours(2)->addMinutes(30),
        'status' => AppointmentStatus::Confirmed->value,
    ]);

    $firstRun = app(ReminderDispatcherService::class)->dispatch();
    $secondRun = app(ReminderDispatcherService::class)->dispatch();

    expect($firstRun[ReminderType::TwoHours->value])->toBe(1)
        ->and($secondRun[ReminderType::TwoHours->value])->toBe(0)
        ->and(AppointmentReminder::query()->where('reminder_type', ReminderType::TwoHours->value)->count())->toBe(1);
});
