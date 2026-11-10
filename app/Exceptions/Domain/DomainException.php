<?php

namespace App\Exceptions\Domain;

use RuntimeException;

// ожидаемая бизнес-ошибка, текст можно показывать юзеру
abstract class DomainException extends RuntimeException
{
    abstract public function userMessage(): string;

    public function status(): int
    {
        return 409;
    }
}
