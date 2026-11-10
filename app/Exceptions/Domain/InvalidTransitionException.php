<?php

namespace App\Exceptions\Domain;

use App\Enums\RequestStatus;

class InvalidTransitionException extends DomainException
{
    public function __construct(
        public readonly RequestStatus $from,
        public readonly RequestStatus $to,
    ) {
        parent::__construct("Transition {$from->value} -> {$to->value} is not allowed");
    }

    public function userMessage(): string
    {
        return "Заявка в статусе «{$this->from->label()}» не может перейти в «{$this->to->label()}».";
    }
}
