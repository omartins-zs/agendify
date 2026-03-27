<?php

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Company;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;

if (! function_exists('makeCompany')) {
    function makeCompany(array $attributes = [], array $settings = []): Company
    {
        $company = Company::factory()->create($attributes);

        if ($settings !== []) {
            $company->settings()->update($settings);
        }

        return $company->fresh()->load('settings');
    }
}

if (! function_exists('makeUser')) {
    function makeUser(Company $company, UserRole $role = UserRole::Owner, array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'company_id' => $company->id,
            'role' => $role,
            'status' => UserStatus::Active,
            'email_verified_at' => now(),
        ], $attributes));
    }
}

if (! function_exists('makeService')) {
    function makeService(Company $company, array $attributes = []): Service
    {
        return Service::query()->create(array_merge([
            'company_id' => $company->id,
            'name' => 'Servico '.fake()->unique()->word(),
            'duration_minutes' => 30,
            'price' => 50,
            'is_active' => true,
        ], $attributes));
    }
}

if (! function_exists('makeClient')) {
    function makeClient(Company $company, array $attributes = []): Client
    {
        return Client::query()->create(array_merge([
            'company_id' => $company->id,
            'name' => fake()->name(),
            'phone' => fake()->unique()->numerify('119########'),
            'email' => fake()->safeEmail(),
        ], $attributes));
    }
}

if (! function_exists('makeAppointment')) {
    function makeAppointment(
        Company $company,
        ?Service $service = null,
        ?Client $client = null,
        array $attributes = [],
    ): Appointment {
        $service ??= makeService($company);
        $client ??= makeClient($company);

        $startsAt = $attributes['starts_at'] ?? CarbonImmutable::now('UTC')->addDay()->startOfHour();
        $startsAt = $startsAt instanceof CarbonImmutable ? $startsAt : CarbonImmutable::parse((string) $startsAt);

        $endsAt = $attributes['ends_at'] ?? $startsAt->addMinutes((int) $service->duration_minutes ?: 30);

        return Appointment::query()->create(array_merge([
            'company_id' => $company->id,
            'service_id' => $service->id,
            'client_id' => $client->id,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => AppointmentStatus::Scheduled->value,
            'source' => 'dashboard',
            'confirmation_token' => fake()->regexify('[A-Za-z0-9]{48}'),
        ], $attributes));
    }
}
