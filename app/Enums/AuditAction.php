<?php

namespace App\Enums;

enum AuditAction: string
{
    case Created = 'created';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Заявка создана',
            self::Submitted => 'Отправлена на согласование',
            self::Approved => 'Согласовано',
            self::Rejected => 'Отклонено',
            self::Cancelled => 'Заявка отменена',
        };
    }
}
