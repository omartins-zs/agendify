<?php

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Company;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('prevents double booking for the same time window', function () {
    $company = Company::factory()->create();

    $user = User::factory()->create([
        'company_id' => $company->id,
        'role' => UserRole::Owner,
        'status' => UserStatus::Active,
        'email_verified_at' => now(),
    ]);

    $service = Service::query()->create([
        'company_id' => $company->id,
        'name' => 'Consulta',
        'duration_minutes' => 45,
        'is_active' => true,
    ]);

    $firstClient = Client::query()->create([
        'company_id' => $company->id,
        'name' => 'Cliente 1',
        'phone' => '11999990001',
    ]);

    $secondClient = Client::query()->create([
        'company_id' => $company->id,
        'name' => 'Cliente 2',
        'phone' => '11999990002',
    ]);

    $startsAt = now()->addDay()->setTime(10, 0)->toIso8601String();

    $this->actingAs($user)
        ->post(route('appointments.store'), [
            'service_id' => $service->id,
            'client_id' => $firstClient->id,
            'date' => now()->addDay()->format('Y-m-d'),
            'starts_at' => $startsAt,
        ])
        ->assertSessionHasNoErrors();

    $this->actingAs($user)
        ->post(route('appointments.store'), [
            'service_id' => $service->id,
            'client_id' => $secondClient->id,
            'date' => now()->addDay()->format('Y-m-d'),
            'starts_at' => $startsAt,
        ])
        ->assertSessionHasErrors('starts_at');

    expect(Appointment::query()->forCompany($company->id)->count())->toBe(1);
});
