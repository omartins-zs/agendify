<?php

use App\Enums\AppointmentStatus;
use App\Enums\ReminderType;
use App\Jobs\SendAppointmentReminderJob;
use App\Models\AppointmentReminder;
use App\Notifications\AppointmentReminderNotification;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('sends reminder for active appointment and marks reminder as dispatched', function () {
    Notification::fake();

    $company = makeCompany();
    $service = makeService($company);
    $client = makeClient($company, ['email' => 'client@example.com']);

    $appointment = makeAppointment($company, $service, $client, [
        'starts_at' => CarbonImmutable::now('UTC')->addHours(2),
        'ends_at' => CarbonImmutable::now('UTC')->addHours(2)->addMinutes(30),
        'status' => AppointmentStatus::Confirmed->value,
    ]);

    $reminder = AppointmentReminder::query()->create([
        'company_id' => $company->id,
        'appointment_id' => $appointment->id,
        'reminder_type' => ReminderType::TwoHours->value,
        'scheduled_for' => now()->addHours(2),
        'channel' => 'mail',
        'dispatched_at' => null,
    ]);

    (new SendAppointmentReminderJob($appointment->id, ReminderType::TwoHours->value))->handle();

    Notification::assertSentOnDemand(AppointmentReminderNotification::class);
    expect($reminder->fresh()->dispatched_at)->not->toBeNull();
});

it('does not send reminder for cancelled appointment', function () {
    Notification::fake();

    $company = makeCompany();
    $service = makeService($company);
    $client = makeClient($company, ['email' => 'client@example.com']);

    $appointment = makeAppointment($company, $service, $client, [
        'status' => AppointmentStatus::Cancelled->value,
    ]);

    $reminder = AppointmentReminder::query()->create([
        'company_id' => $company->id,
        'appointment_id' => $appointment->id,
        'reminder_type' => ReminderType::OneDay->value,
        'scheduled_for' => now()->addDay(),
        'channel' => 'mail',
        'dispatched_at' => null,
    ]);

    (new SendAppointmentReminderJob($appointment->id, ReminderType::OneDay->value))->handle();

    Notification::assertNothingSent();
    expect($reminder->fresh()->dispatched_at)->toBeNull();
});
