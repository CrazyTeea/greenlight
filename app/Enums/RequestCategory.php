<?php

namespace App\Enums;

enum RequestCategory: string
{
    case Office = 'office';
    case It = 'it';
    case Services = 'services';
    case Marketing = 'marketing';
    case Hr = 'hr';

    public function label(): string
    {
        return match ($this) {
            self::Office => 'Офис и канцелярия',
            self::It => 'IT и оборудование',
            self::Services => 'Услуги и подписки',
            self::Marketing => 'Маркетинг',
            self::Hr => 'HR и обучение',
        };
    }
}
