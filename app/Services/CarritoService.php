<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Carrito;

class CarritoService
{
    public function obtenerCarrito(string $idUsuario): Carrito
    {
        return Carrito::with('items.producto')
            ->firstOrCreate(
                ['id_usuario' => $idUsuario],
                ['id_sucursal_activa' => null]
            );
    }
}
