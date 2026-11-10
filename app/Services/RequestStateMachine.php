<?php

namespace App\Services;

use App\Enums\RequestStatus;
use App\Exceptions\Domain\InvalidTransitionException;

// какие переходы разрешены. матрица плоская, домен маленький,
// городить тут стейт-машину с плагинами смысла нет
final class RequestStateMachine
{
    private const ALLOWED = [
        'draft' => ['pending', 'cancelled'],
        'pending' => ['approved', 'rejected', 'cancelled'],
        'approved' => [],
        'rejected' => [],
        'cancelled' => [],
    ];

    public function can(RequestStatus $from, RequestStatus $to): bool
    {
        return in_array($to->value, self::ALLOWED[$from->value] ?? [], true);
    }

    public function assertCan(RequestStatus $from, RequestStatus $to): void
    {
        if (! $this->can($from, $to)) {
            throw new InvalidTransitionException($from, $to);
        }
    }
}
