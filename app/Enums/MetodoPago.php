<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\EnumUtils;

enum MetodoPago: string
{
    use EnumUtils;

    case EFECTIVO = 'efectivo';
    case TARJETA_CREDITO = 'tarjeta_credito';
    case TARJETA_DEBITO = 'tarjeta_debito';
    case QR_BANCARIO = 'qr_bancario';
    case BILLETERA_MOVIL = 'billetera_movil';

    public function label(): string
    {
        return match($this) {
            self::EFECTIVO => 'Efectivo contra entrega',
            self::TARJETA_CREDITO => 'Tarjeta de Crédito',
            self::TARJETA_DEBITO => 'Tarjeta de Débito',
            self::QR_BANCARIO => 'Pago con QR (Simple/Banco)',
            self::BILLETERA_MOVIL => 'Billetera Móvil (TigoMoney/Solibag)',
        };
    }
}
