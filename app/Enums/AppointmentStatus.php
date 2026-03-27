<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Scheduled = 'agendado';
    case Confirmed = 'confirmado';
    case Done = 'realizado';
    case NoShow = 'faltou';
    case Cancelled = 'cancelado';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Agendado',
            self::Confirmed => 'Confirmado',
            self::Done => 'Realizado',
            self::NoShow => 'Faltou',
            self::Cancelled => 'Cancelado',
        };
    }

    public function isActiveBooking(): bool
    {
        return in_array($this, [self::Scheduled, self::Confirmed], true);
    }
}
