<?php

namespace App\Enums;

enum ReminderType: string
{
    case OneDay = '24h';
    case TwoHours = '2h';

    public function minutesBefore(): int
    {
        return match ($this) {
            self::OneDay => 24 * 60,
            self::TwoHours => 2 * 60,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::OneDay => '24 horas antes',
            self::TwoHours => '2 horas antes',
        };
    }
}
