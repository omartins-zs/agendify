<?php

use App\Actions\CreateAppointmentAction;
use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use App\Notifications\AppointmentCreatedNotification;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('creates a confirmed appointment when auto confirmation is enabled', function () {
    Notification::fake();

    $company = makeCompany(settings: [
        'auto_confirm_bookings' => true,
        'timezone' => 'America/Sao_Paulo',
    ]);

    $user = makeUser($company, UserRole::Owner);
    $service = makeService($company, ['duration_minutes' => 45]);
    $client = makeClient($company, ['email' => 'client@example.com']);

    $appointment = app(CreateAppointmentAction::class)->execute(
        company: $company,
        service: $service,
        client: $client,
        startsAt: '2026-03-30 10:00:00',
        bookedBy: $user,
        source: 'dashboard',
    );

    expect($appointment->status)->toBe(AppointmentStatus::Confirmed)
        ->and($appointment->confirmed_at)->not->toBeNull()
        ->and((int) $appointment->starts_at->diffInMinutes($appointment->ends_at))->toBe(45);

    expect(AppointmentStatusHistory::query()
        ->where('appointment_id', $appointment->id)
        ->where('to_status', AppointmentStatus::Confirmed->value)
        ->exists())->toBeTrue();

    Notification::assertSentOnDemand(AppointmentCreatedNotification::class);
});

it('creates a scheduled appointment when auto confirmation is disabled', function () {
    $company = makeCompany(settings: ['auto_confirm_bookings' => false]);
    $service = makeService($company);
    $client = makeClient($company);

    $appointment = app(CreateAppointmentAction::class)->execute(
        company: $company,
        service: $service,
        client: $client,
        startsAt: CarbonImmutable::now('UTC')->addDay()->setTime(9, 0),
    );

    expect($appointment->status)->toBe(AppointmentStatus::Scheduled)
        ->and($appointment->confirmed_at)->toBeNull();
});

it('rejects creating appointment with inactive service', function () {
    $company = makeCompany();
    $service = makeService($company, ['is_active' => false]);
    $client = makeClient($company);

    try {
        app(CreateAppointmentAction::class)->execute(
            company: $company,
            service: $service,
            client: $client,
            startsAt: CarbonImmutable::now('UTC')->addDay()->setTime(9, 0),
        );

        $this->fail('Expected ValidationException was not thrown.');
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey('service_id');
    }
});

it('rejects creating appointment with client from another company', function () {
    $company = makeCompany();
    $otherCompany = makeCompany();

    $service = makeService($company);
    $foreignClient = makeClient($otherCompany);

    try {
        app(CreateAppointmentAction::class)->execute(
            company: $company,
            service: $service,
            client: $foreignClient,
            startsAt: CarbonImmutable::now('UTC')->addDay()->setTime(9, 0),
        );

        $this->fail('Expected ValidationException was not thrown.');
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey('client_id');
    }
});

it('prevents overlapping appointments for active bookings', function () {
    $company = makeCompany();
    $service = makeService($company, ['duration_minutes' => 30]);
    $clientA = makeClient($company);
    $clientB = makeClient($company);

    $start = CarbonImmutable::now('UTC')->addDay()->setTime(10, 0);

    $action = app(CreateAppointmentAction::class);

    $action->execute($company, $service, $clientA, $start);

    try {
        $action->execute($company, $service, $clientB, $start->addMinutes(15));
        $this->fail('Expected ValidationException was not thrown.');
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey('starts_at');
    }

    expect(Appointment::query()->forCompany($company->id)->count())->toBe(1);
});
