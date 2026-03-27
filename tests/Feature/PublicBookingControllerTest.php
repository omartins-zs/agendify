<?php

use App\Enums\AppointmentStatus;
use App\Models\AvailabilityRule;
use App\Models\BlockedTime;
use Carbon\CarbonImmutable;
use Inertia\Testing\AssertableInertia;

it('renders public booking page with only active services', function () {
    $company = makeCompany(settings: ['timezone' => 'America/Sao_Paulo']);

    makeService($company, ['name' => 'Corte', 'is_active' => true]);
    makeService($company, ['name' => 'Inativo', 'is_active' => false]);

    $this->get(route('booking.show', $company->slug))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Booking/Show')
            ->where('company.slug', $company->slug)
            ->has('services', 1)
            ->where('services.0.name', 'Corte')
        );
});

it('returns available slots for public booking endpoint', function () {
    $company = makeCompany(settings: ['timezone' => 'America/Sao_Paulo']);
    $service = makeService($company, ['duration_minutes' => 30]);
    $client = makeClient($company);

    $date = CarbonImmutable::parse('2026-03-30', 'America/Sao_Paulo');

    AvailabilityRule::query()->create([
        'company_id' => $company->id,
        'weekday' => $date->dayOfWeek,
        'start_time' => '09:00:00',
        'end_time' => '10:30:00',
        'slot_interval_minutes' => 30,
        'is_active' => true,
    ]);

    makeAppointment($company, $service, $client, [
        'starts_at' => $date->setTime(9, 30)->utc(),
        'ends_at' => $date->setTime(10, 0)->utc(),
        'status' => AppointmentStatus::Confirmed->value,
    ]);

    BlockedTime::query()->create([
        'company_id' => $company->id,
        'starts_at' => $date->setTime(10, 0)->utc(),
        'ends_at' => $date->setTime(10, 30)->utc(),
    ]);

    $response = $this->getJson(route('booking.slots', [
        'company' => $company->slug,
        'service_id' => $service->id,
        'date' => $date->format('Y-m-d'),
    ]));

    $response->assertOk();
    expect($response->json('slots'))->toHaveCount(1)
        ->and($response->json('slots.0.label'))->toBe('09:00');
});

it('creates appointment from public page and redirects to success', function () {
    $company = makeCompany(settings: ['timezone' => 'America/Sao_Paulo']);
    $service = makeService($company);

    $startsAt = CarbonImmutable::now('America/Sao_Paulo')->addDay()->setTime(10, 0)->toIso8601String();

    $response = $this->post(route('booking.store', ['company' => $company->slug]), [
        'service_id' => $service->id,
        'starts_at' => $startsAt,
        'client_name' => 'Cliente Publico',
        'client_phone' => '11999998888',
        'client_email' => 'publico@example.com',
        'notes' => 'Primeiro atendimento',
    ]);

    $appointment = \App\Models\Appointment::query()->firstOrFail();

    $response->assertRedirect(route('booking.success', [
        'company' => $company->slug,
        'appointment' => $appointment->id,
    ]));

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'company_id' => $company->id,
        'service_id' => $service->id,
        'source' => 'public_link',
    ]);
});

it('rejects public cancellation with invalid token', function () {
    $company = makeCompany();
    $appointment = makeAppointment($company, attributes: [
        'status' => AppointmentStatus::Scheduled->value,
        'confirmation_token' => 'valid-token',
    ]);

    $this->post(route('booking.cancel', [
        'company' => $company->slug,
        'appointment' => $appointment->id,
    ]), [
        'token' => 'invalid-token',
    ])->assertForbidden();
});

it('blocks cancellation when cancellation window is closed', function () {
    $company = makeCompany(settings: [
        'allow_client_cancellation' => true,
        'cancellation_window_hours' => 12,
        'timezone' => 'America/Sao_Paulo',
    ]);

    $appointment = makeAppointment($company, attributes: [
        'status' => AppointmentStatus::Confirmed->value,
        'starts_at' => CarbonImmutable::now('America/Sao_Paulo')->addHours(2)->utc(),
        'confirmation_token' => 'cancel-token',
    ]);

    $this->from(route('booking.success', [
        'company' => $company->slug,
        'appointment' => $appointment->id,
    ]))->post(route('booking.cancel', [
        'company' => $company->slug,
        'appointment' => $appointment->id,
    ]), [
        'token' => 'cancel-token',
    ])->assertSessionHas('error');

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Confirmed);
});

it('cancels appointment successfully when token and window are valid', function () {
    $company = makeCompany(settings: [
        'allow_client_cancellation' => true,
        'cancellation_window_hours' => 12,
        'timezone' => 'America/Sao_Paulo',
    ]);

    $appointment = makeAppointment($company, attributes: [
        'status' => AppointmentStatus::Scheduled->value,
        'starts_at' => CarbonImmutable::now('America/Sao_Paulo')->addDay()->utc(),
        'confirmation_token' => 'cancel-token',
    ]);

    $this->from(route('booking.success', [
        'company' => $company->slug,
        'appointment' => $appointment->id,
    ]))->post(route('booking.cancel', [
        'company' => $company->slug,
        'appointment' => $appointment->id,
    ]), [
        'token' => 'cancel-token',
    ])->assertSessionHas('success');

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Cancelled);
});
