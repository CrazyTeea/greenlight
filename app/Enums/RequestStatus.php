<?php

namespace App\Enums;

enum RequestStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Черновик',
            self::Pending => 'На согласовании',
            self::Approved => 'Согласована',
            self::Rejected => 'Отклонена',
            self::Cancelled => 'Отменена',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Approved, self::Rejected, self::Cancelled], true);
    }
}
