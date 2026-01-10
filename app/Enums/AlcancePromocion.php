<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\EnumUtils;

enum AlcancePromocion: string
{
    use EnumUtils;

    case PRODUCTO = 'producto';
    case SUCURSAL = 'sucursal';
    case GLOBAL = 'global';

    public function label(): string
    {
        return match($this) {
            self::PRODUCTO => 'Aplica a Producto Específico',
            self::SUCURSAL => 'Válido en Sucursal Específica',
            self::GLOBAL => 'Válido en toda la Plataforma',
        };
    }
}
