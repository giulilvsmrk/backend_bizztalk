<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\EnumUtils;

enum TipoBeneficio: string
{
    use EnumUtils;

    case PORCENTAJE = 'porcentaje';
    case MONTO_FIJO = 'monto_fijo';
    case ENVIO_GRATIS = 'envio_gratis';

    public function label(): string
    {
        return match($this) {
            self::PORCENTAJE => 'Descuento Porcentual (%)',
            self::MONTO_FIJO => 'Descuento Fijo (Monto)',
            self::ENVIO_GRATIS => 'Envío Gratuito',
        };
    }
}
