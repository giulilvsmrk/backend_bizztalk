<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\EnumUtils;

enum EstadoTransaccion: string
{
    use EnumUtils;

    case PENDIENTE = 'pendiente';
    case APROBADO = 'aprobado';
    case RECHAZADO = 'rechazado';
    case REEMBOLSADO = 'reembolsado';
    case EXPIRADO = 'expirado';

    public function label(): string
    {
        return match($this) {
            self::PENDIENTE => 'Procesando Pago',
            self::APROBADO => 'Pago Exitoso',
            self::RECHAZADO => 'Pago Rechazado',
            self::REEMBOLSADO => 'Reembolsado',
            self::EXPIRADO => 'Expiró el tiempo',
        };
    }
}
