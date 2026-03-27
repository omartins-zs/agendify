<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function view(User $user, Client $client): bool
    {
        return $user->isActive() && $user->company_id === $client->company_id;
    }

    public function create(User $user): bool
    {
        return $user->isActive() && $user->canManageClients();
    }

    public function update(User $user, Client $client): bool
    {
        return $user->company_id === $client->company_id && $user->canManageClients();
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->company_id === $client->company_id && $user->canManageClients();
    }
}
