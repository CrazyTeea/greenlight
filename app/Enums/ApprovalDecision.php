<?php

namespace App\Enums;

enum ApprovalDecision: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Ожидает решения',
            self::Approved => 'Согласовано',
            self::Rejected => 'Отклонено',
        };
    }
}
