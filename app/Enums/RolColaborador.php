<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\EnumUtils;

enum RolColaborador: string
{
    use EnumUtils;

    case EMPLEADO = 'empleado';
    case ENCARGADO = 'encargado';

    public function label(): string
    {
        return match($this) {
            self::EMPLEADO => 'Personal Operativo',
            self::ENCARGADO => 'Gerente de Sucursal',
        };
    }
}
