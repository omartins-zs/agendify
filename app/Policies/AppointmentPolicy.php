<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function view(User $user, Appointment $appointment): bool
    {
        return $user->isActive() && $user->company_id === $appointment->company_id;
    }

    public function create(User $user): bool
    {
        return $user->isActive() && $user->canManageAppointments();
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $user->company_id === $appointment->company_id && $user->canManageAppointments();
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->company_id === $appointment->company_id && $user->canManageAppointments();
    }
}
