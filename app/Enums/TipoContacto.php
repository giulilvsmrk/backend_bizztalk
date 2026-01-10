<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\EnumUtils;

enum TipoContacto: string
{
    use EnumUtils;

    case WHATSAPP = 'whatsapp';
    case FIJO = 'fijo';
    case MOVIL = 'movil';
    case FAX = 'fax';
    case CALL_CENTER = 'call_center';

    public function label(): string
    {
        return match($this) {
            self::WHATSAPP => 'WhatsApp Business',
            self::FIJO => 'Teléfono Fijo',
            self::MOVIL => 'Celular',
            self::FAX => 'Fax',
            self::CALL_CENTER => 'Central Telefónica',
        };
    }
}
