<?php
declare(strict_types=1);

namespace App\Actions\Carrito;

use App\Models\Carrito;

class VaciarCarritoAction
{
    public function execute(string $idUsuario): void
    {
        $carrito = Carrito::firstOrCreate(
            ['id_usuario' => $idUsuario],
            ['id_sucursal_activa' => null]
        );
        $carrito->vaciar();
    }
}
