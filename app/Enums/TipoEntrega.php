<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\EnumUtils;

enum TipoEntrega: string
{
    use EnumUtils;

    case DELIVERY = 'delivery';
    case RECOJO = 'recojo';
    case MESA = 'mesa';

    public function label(): string
    {
        return match($this) {
            self::DELIVERY => 'Envío a Domicilio',
            self::RECOJO => 'Recojo en Tienda (Pick-up)',
            self::MESA => 'Consumo en Local',
        };
    }
}
