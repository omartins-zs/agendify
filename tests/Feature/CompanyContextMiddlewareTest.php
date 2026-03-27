<?php

use App\Enums\UserStatus;
use Symfony\Component\HttpFoundation\Response;

it('blocks inactive users from accessing company protected routes', function () {
    $company = makeCompany();
    $inactiveUser = makeUser($company, attributes: [
        'status' => UserStatus::Inactive,
    ]);

    $this->actingAs($inactiveUser)
        ->get(route('dashboard'))
        ->assertStatus(Response::HTTP_FORBIDDEN);
});

it('blocks users without company id from protected routes', function () {
    $company = makeCompany();
    $userWithoutCompany = makeUser($company, attributes: [
        'company_id' => null,
    ]);

    $this->actingAs($userWithoutCompany)
        ->get(route('dashboard'))
        ->assertStatus(Response::HTTP_FORBIDDEN);
});
