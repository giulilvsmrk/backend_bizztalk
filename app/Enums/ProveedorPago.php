<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\EnumUtils;

enum ProveedorPago: string
{
    use EnumUtils;

    case STRIPE = 'stripe';
    case CYBERSOURCE = 'cybersource';
    case LIBELULA = 'libelula';
    case PASARELA_QR_LOCAL = 'pasarela_qr_local';
    case EFECTIVO_MANUAL = 'efectivo_manual';
    case SIMULADO = 'simulado'; // Para entornos dev
    case TRANSFERENCIA_MANUAL = 'transferencia_manual';

    public function label(): string
    {
        return match($this) {
            self::STRIPE => 'Stripe Payments',
            self::CYBERSOURCE => 'Cybersource (Visa)',
            self::LIBELULA => 'Libélula (Bolivia)',
            self::PASARELA_QR_LOCAL => 'Integración QR Nacional',
            self::EFECTIVO_MANUAL => 'Cobro Manual',
            self::SIMULADO => 'Entorno de Pruebas',
            self::TRANSFERENCIA_MANUAL => 'Transferencia Bancaria Directa',
        };
    }
}
