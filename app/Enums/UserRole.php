<?php

namespace App\Enums;

enum UserRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Attendant = 'attendant';
    case Viewer = 'viewer';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Admin => 'Admin',
            self::Attendant => 'Atendente',
            self::Viewer => 'Viewer',
        };
    }

    public function canManageServices(): bool
    {
        return in_array($this, [self::Owner, self::Admin], true);
    }

    public function canManageAppointments(): bool
    {
        return in_array($this, [self::Owner, self::Admin, self::Attendant], true);
    }

    public function canManageClients(): bool
    {
        return in_array($this, [self::Owner, self::Admin, self::Attendant], true);
    }

    public function canManageAvailability(): bool
    {
        return in_array($this, [self::Owner, self::Admin], true);
    }
}
