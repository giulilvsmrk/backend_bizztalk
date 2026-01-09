<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\EnumUtils;

enum TipoVehiculo: string
{
    use EnumUtils;

    case MOTO = 'moto';
    case BICICLETA = 'bicicleta';
    case AUTO = 'auto';
    case CAMION = 'camion';
    case PIE = 'pie';
    case DRONE = 'drone';

    public function label(): string
    {
        return match($this) {
            self::MOTO => 'Motocicleta',
            self::BICICLETA => 'Bicicleta',
            self::AUTO => 'Automóvil',
            self::CAMION => 'Camión de Carga',
            self::PIE => 'A Pie / Caminando',
            self::DRONE => 'Drone Aéreo',
        };
    }
}
