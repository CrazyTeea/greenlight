<?php

namespace App\Exceptions\Domain;

class FourEyesException extends DomainException
{
    public function status(): int
    {
        return 403;
    }

    public function userMessage(): string
    {
        return 'Нельзя согласовать собственную заявку, нужен независимый проверяющий.';
    }
}
