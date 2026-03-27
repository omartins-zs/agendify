<?php

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Models\AvailabilityRule;
use Carbon\CarbonImmutable;

it('allows attendant to create appointment with existing client', function () {
    $company = makeCompany();
    $user = makeUser($company, UserRole::Attendant);
    $service = makeService($company);
    $client = makeClient($company);

    $startsAt = CarbonImmutable::now('America/Sao_Paulo')->addDay()->setTime(10, 0)->toIso8601String();

    $this->actingAs($user)->post(route('appointments.store'), [
        'service_id' => $service->id,
        'client_id' => $client->id,
        'starts_at' => $startsAt,
    ])->assertSessionHasNoErrors()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('appointments', [
        'company_id' => $company->id,
        'service_id' => $service->id,
        'client_id' => $client->id,
        'source' => 'dashboard',
    ]);
});

it('creates a new client automatically when client id is not provided', function () {
    $company = makeCompany();
    $user = makeUser($company, UserRole::Admin);
    $service = makeService($company);

    $startsAt = CarbonImmutable::now('America/Sao_Paulo')->addDay()->setTime(11, 0)->toIso8601String();

    $this->actingAs($user)->post(route('appointments.store'), [
        'service_id' => $service->id,
        'starts_at' => $startsAt,
        'client_name' => 'Novo Cliente',
        'client_phone' => '11999888777',
        'client_email' => 'novo@cliente.com',
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('clients', [
        'company_id' => $company->id,
        'phone' => '11999888777',
        'name' => 'Novo Cliente',
    ]);
});

it('forbids viewer from creating appointments', function () {
    $company = makeCompany();
    $viewer = makeUser($company, UserRole::Viewer);
    $service = makeService($company);

    $this->actingAs($viewer)->post(route('appointments.store'), [
        'service_id' => $service->id,
        'starts_at' => CarbonImmutable::now('America/Sao_Paulo')->addDay()->setTime(9, 0)->toIso8601String(),
        'client_name' => 'Sem Permissao',
        'client_phone' => '11999990000',
    ])->assertForbidden();
});

it('forbids updating appointment from another company', function () {
    $companyA = makeCompany();
    $companyB = makeCompany();
    $user = makeUser($companyA, UserRole::Admin);
    $appointment = makeAppointment($companyB, attributes: [
        'status' => AppointmentStatus::Scheduled->value,
    ]);

    $this->actingAs($user)->patch(route('appointments.update', $appointment), [
        'status' => AppointmentStatus::Confirmed->value,
    ])->assertForbidden();
});

it('cancels appointment instead of deleting record', function () {
    $company = makeCompany();
    $user = makeUser($company, UserRole::Owner);
    $appointment = makeAppointment($company, attributes: [
        'status' => AppointmentStatus::Scheduled->value,
    ]);

    $this->actingAs($user)->delete(route('appointments.destroy', $appointment))
        ->assertSessionHas('success');

    $appointment->refresh();

    expect($appointment->status)->toBe(AppointmentStatus::Cancelled);
});

it('returns slot suggestions in authenticated endpoint', function () {
    $company = makeCompany(settings: ['timezone' => 'America/Sao_Paulo']);
    $user = makeUser($company, UserRole::Admin);
    $service = makeService($company, ['duration_minutes' => 30]);

    $date = CarbonImmutable::parse('2026-03-30', 'America/Sao_Paulo');

    AvailabilityRule::query()->create([
        'company_id' => $company->id,
        'weekday' => $date->dayOfWeek,
        'start_time' => '08:00:00',
        'end_time' => '09:00:00',
        'slot_interval_minutes' => 30,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->getJson(route('appointments.slots', [
        'service_id' => $service->id,
        'date' => $date->format('Y-m-d'),
    ]));

    $response->assertOk();
    expect($response->json('slots'))->toHaveCount(2);
});
