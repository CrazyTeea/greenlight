<?php

namespace App\Exceptions\Domain;

class NotRequestOwnerException extends DomainException
{
    public function status(): int
    {
        return 403;
    }

    public function userMessage(): string
    {
        return 'Это действие доступно только автору заявки.';
    }
}
