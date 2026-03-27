<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Company;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $company = Company::query()->updateOrCreate([
            'slug' => 'agendify-demo',
        ], [
            'name' => 'Agendify Demo',
            'phone' => '(11) 99999-0000',
            'email' => 'demo@agendify.com',
            'timezone' => 'America/Sao_Paulo',
        ]);

        $owner = User::query()->updateOrCreate([
            'email' => 'test@example.com',
        ], [
            'company_id' => $company->id,
            'name' => 'Owner Demo',
            'password' => bcrypt('password'),
            'role' => UserRole::Owner,
            'status' => UserStatus::Active,
            'email_verified_at' => now(),
        ]);

        foreach ([
            [
                'name' => 'Corte de Cabelo',
                'duration_minutes' => 30,
                'price' => 45.00,
                'is_active' => true,
            ],
            [
                'name' => 'Barba Completa',
                'duration_minutes' => 40,
                'price' => 55.00,
                'is_active' => true,
            ],
        ] as $serviceData) {
            Service::query()->updateOrCreate([
                'company_id' => $company->id,
                'name' => $serviceData['name'],
            ], [
                'duration_minutes' => $serviceData['duration_minutes'],
                'price' => $serviceData['price'],
                'is_active' => $serviceData['is_active'],
            ]);
        }

        $firstService = Service::query()->forCompany($company->id)->orderBy('id')->firstOrFail();

        $client = Client::query()->updateOrCreate([
            'company_id' => $company->id,
            'phone' => '(11) 98888-7777',
        ], [
            'name' => 'Cliente Exemplo',
            'email' => 'cliente@example.com',
        ]);

        $startsAt = now()->addDay()->setHour(10)->startOfHour();
        $endsAt = (clone $startsAt)->addMinutes($firstService->duration_minutes);

        Appointment::query()->updateOrCreate([
            'company_id' => $company->id,
            'starts_at' => $startsAt,
        ], [
            'service_id' => $firstService->id,
            'client_id' => $client->id,
            'user_id' => $owner->id,
            'ends_at' => $endsAt,
            'status' => AppointmentStatus::Confirmed,
            'source' => 'dashboard',
            'confirmation_token' => Str::random(48),
            'confirmed_at' => now(),
        ]);
    }
}
