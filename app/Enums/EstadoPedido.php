<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\EnumUtils;

enum EstadoPedido: string
{
    use EnumUtils;

    case PENDIENTE = 'pendiente';
    case PAGADO = 'pagado';
    case PREPARANDO = 'preparando';
    case EN_CAMINO = 'en_camino';
    case ENTREGADO = 'entregado';
    case CANCELADO = 'cancelado';

    public function label(): string
    {
        return match($this) {
            self::PENDIENTE => 'Pendiente de Confirmación',
            self::PAGADO => 'Pagado',
            self::PREPARANDO => 'En Preparación',
            self::EN_CAMINO => 'En Camino',
            self::ENTREGADO => 'Entregado',
            self::CANCELADO => 'Cancelado',
        };
    }
}
