<?php

declare(strict_types=1);

namespace App\Enums;

enum TipoPromocion: string
{
    case PORCENTAJE = 'porcentaje';
    case MONTO_FIJO = 'monto_fijo';
    case ENVIO_GRATIS = 'envio_gratis';

    public function label(): string
    {
        return match($this) {
            self::PORCENTAJE => 'Descuento Porcentual (%)',
            self::MONTO_FIJO => 'Descuento Fijo ($)',
            self::ENVIO_GRATIS => 'Envío Gratuito',
        };
    }

    public function simbolo(): string
    {
        return match($this) {
            self::PORCENTAJE => '%',
            self::MONTO_FIJO => '$',
            self::ENVIO_GRATIS => '🚚',
        };
    }

    /**
     * Helper para listar opciones en un <select> de HTML.
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_column(self::cases(), 'value', 'name');
        // O mapeado manual si prefieres usar el label():
        // return [
        //     self::PORCENTAJE->value => self::PORCENTAJE->label(),
        //     ...
        // ];
    }
}
