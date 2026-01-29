<?php
declare(strict_types=1);

namespace App\Actions\Carrito;

use App\Models\Carrito;
use App\Models\ItemCarrito;

class AgregarItemCarritoAction
{
    public function execute(string $idUsuario, string $idProducto, int $cantidad, ?string $observacion = null): ItemCarrito
    {
        $carrito = Carrito::firstOrCreate(
            ['id_usuario' => $idUsuario],
            ['id_sucursal_activa' => null]
        );

        $item = $carrito->items()->where('id_producto', $idProducto)->first();

        if ($item) {
            $item->cantidad += $cantidad;
            $item->observacion = $observacion ?? $item->observacion;
            $item->save();
        } else {
            $item = $carrito->items()->create([
                'id_producto' => $idProducto,
                'cantidad' => $cantidad,
                'observacion' => $observacion,
            ]);
        }

        $carrito->touch();

        return $item->load('producto');
    }
}
