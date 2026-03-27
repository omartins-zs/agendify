<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function view(User $user, Service $service): bool
    {
        return $user->isActive() && $user->company_id === $service->company_id;
    }

    public function create(User $user): bool
    {
        return $user->isActive() && $user->canManageServices();
    }

    public function update(User $user, Service $service): bool
    {
        return $user->company_id === $service->company_id && $user->canManageServices();
    }

    public function delete(User $user, Service $service): bool
    {
        return $user->company_id === $service->company_id && $user->canManageServices();
    }
}
