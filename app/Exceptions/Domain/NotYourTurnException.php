<?php

namespace App\Exceptions\Domain;

class NotYourTurnException extends DomainException
{
    public function userMessage(): string
    {
        return 'Сейчас решает другой согласующий, заявка уже ушла дальше по маршруту.';
    }
}
