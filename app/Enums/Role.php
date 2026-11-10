<?php

namespace App\Enums;

enum Role: string
{
    case Employee = 'employee';
    case Manager = 'manager';
    case Cfo = 'cfo';

    public function label(): string
    {
        return match ($this) {
            self::Employee => 'Сотрудник',
            self::Manager => 'Руководитель отдела',
            self::Cfo => 'Финансовый директор',
        };
    }

    public function canSeeAllRequests(): bool
    {
        return $this !== self::Employee;
    }
}
