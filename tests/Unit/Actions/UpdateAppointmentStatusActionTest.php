<?php

use App\Actions\UpdateAppointmentStatusAction;
use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Models\AppointmentStatusHistory;
use App\Notifications\AppointmentCancelledNotification;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('confirms an appointment and creates status history', function () {
    $company = makeCompany();
    $user = makeUser($company, UserRole::Admin);
    $appointment = makeAppointment($company, attributes: [
        'status' => AppointmentStatus::Scheduled->value,
        'confirmed_at' => null,
    ]);

    $updated = app(UpdateAppointmentStatusAction::class)->execute(
        appointment: $appointment,
        nextStatus: AppointmentStatus::Confirmed,
        changedBy: $user,
        reason: 'Confirmado no painel',
    );

    expect($updated->status)->toBe(AppointmentStatus::Confirmed)
        ->and($updated->confirmed_at)->not->toBeNull();

    expect(AppointmentStatusHistory::query()
        ->where('appointment_id', $appointment->id)
        ->where('from_status', AppointmentStatus::Scheduled->value)
        ->where('to_status', AppointmentStatus::Confirmed->value)
        ->where('changed_by_user_id', $user->id)
        ->exists())->toBeTrue();
});

it('marks client last visit when appointment is completed', function () {
    $company = makeCompany();
    $client = makeClient($company, ['last_visit_at' => null]);
    $appointment = makeAppointment($company, client: $client, attributes: [
        'status' => AppointmentStatus::Confirmed->value,
    ]);

    app(UpdateAppointmentStatusAction::class)->execute(
        appointment: $appointment,
        nextStatus: AppointmentStatus::Done,
    );

    expect($client->fresh()->last_visit_at)->not->toBeNull();
});

it('cancels appointment and sends cancellation notification', function () {
    Notification::fake();

    $company = makeCompany();
    $client = makeClient($company, ['email' => 'client@example.com']);
    $appointment = makeAppointment($company, client: $client, attributes: [
        'status' => AppointmentStatus::Scheduled->value,
        'starts_at' => CarbonImmutable::now('UTC')->addDay()->setTime(12, 0),
    ]);

    $updated = app(UpdateAppointmentStatusAction::class)->execute(
        appointment: $appointment,
        nextStatus: AppointmentStatus::Cancelled,
        reason: 'Cancelado pelo cliente',
    );

    expect($updated->status)->toBe(AppointmentStatus::Cancelled)
        ->and($updated->cancelled_at)->not->toBeNull();

    Notification::assertSentOnDemand(AppointmentCancelledNotification::class);
});

it('blocks invalid status transitions', function () {
    $company = makeCompany();
    $appointment = makeAppointment($company, attributes: [
        'status' => AppointmentStatus::Cancelled->value,
    ]);

    expect(fn () => app(UpdateAppointmentStatusAction::class)->execute(
        appointment: $appointment,
        nextStatus: AppointmentStatus::Done,
    ))->toThrow(ValidationException::class);
});

it('returns same appointment when target status is already current', function () {
    $company = makeCompany();
    $appointment = makeAppointment($company, attributes: [
        'status' => AppointmentStatus::Confirmed->value,
    ]);

    $result = app(UpdateAppointmentStatusAction::class)->execute(
        appointment: $appointment,
        nextStatus: AppointmentStatus::Confirmed,
    );

    expect($result->id)->toBe($appointment->id)
        ->and(AppointmentStatusHistory::query()->where('appointment_id', $appointment->id)->count())->toBe(0);
});
