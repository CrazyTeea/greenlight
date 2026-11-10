<?php

namespace App\Exceptions\Domain;

class StaleRequestException extends DomainException
{
    public function userMessage(): string
    {
        return 'Заявку успели изменить в другой вкладке. Обновите страницу и повторите действие.';
    }
}
