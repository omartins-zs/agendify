<?php

use App\Enums\UserRole;

it('forbids viewer from creating services', function () {
    $company = makeCompany();
    $viewer = makeUser($company, UserRole::Viewer);

    $this->actingAs($viewer)->post(route('services.store'), [
        'name' => 'Servico Bloqueado',
        'duration_minutes' => 30,
        'price' => 40,
        'is_active' => true,
    ])->assertForbidden();
});

it('allows admin to create services', function () {
    $company = makeCompany();
    $admin = makeUser($company, UserRole::Admin);

    $this->actingAs($admin)->post(route('services.store'), [
        'name' => 'Corte Simples',
        'duration_minutes' => 30,
        'price' => 40,
        'is_active' => true,
    ])->assertSessionHasNoErrors()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('services', [
        'company_id' => $company->id,
        'name' => 'Corte Simples',
    ]);
});

it('allows same service name for different companies', function () {
    $companyA = makeCompany();
    $companyB = makeCompany();
    $adminB = makeUser($companyB, UserRole::Admin);

    makeService($companyA, ['name' => 'Padrao']);

    $this->actingAs($adminB)->post(route('services.store'), [
        'name' => 'Padrao',
        'duration_minutes' => 45,
        'price' => 55,
        'is_active' => true,
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('services', [
        'company_id' => $companyB->id,
        'name' => 'Padrao',
    ]);
});

it('forbids updating service from another company', function () {
    $companyA = makeCompany();
    $companyB = makeCompany();
    $admin = makeUser($companyA, UserRole::Admin);
    $foreignService = makeService($companyB);

    $this->actingAs($admin)->patch(route('services.update', $foreignService), [
        'name' => 'Tentativa',
        'duration_minutes' => 20,
        'price' => 10,
        'is_active' => true,
    ])->assertForbidden();
});

it('forbids viewer from creating clients', function () {
    $company = makeCompany();
    $viewer = makeUser($company, UserRole::Viewer);

    $this->actingAs($viewer)->post(route('clients.store'), [
        'name' => 'Cliente Bloqueado',
        'phone' => '11999995555',
        'email' => 'blocked@cliente.com',
    ])->assertForbidden();
});

it('allows attendant to create clients', function () {
    $company = makeCompany();
    $attendant = makeUser($company, UserRole::Attendant);

    $this->actingAs($attendant)->post(route('clients.store'), [
        'name' => 'Cliente Novo',
        'phone' => '11999994444',
        'email' => 'novo@cliente.com',
    ])->assertSessionHasNoErrors()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('clients', [
        'company_id' => $company->id,
        'phone' => '11999994444',
    ]);
});

it('forbids deleting client from another company', function () {
    $companyA = makeCompany();
    $companyB = makeCompany();
    $user = makeUser($companyA, UserRole::Attendant);
    $foreignClient = makeClient($companyB);

    $this->actingAs($user)->delete(route('clients.destroy', $foreignClient))
        ->assertForbidden();
});
