<?php
declare(strict_types=1);

namespace App\Actions\Carrito;

use App\Models\ItemCarrito;

class ActualizarItemCarritoAction
{
    public function execute(int $idItem, int $cantidad, ?string $observacion = null): ItemCarrito
    {
        $item = ItemCarrito::findOrFail($idItem);
        $item->update([
            'cantidad' => $cantidad,
            'observacion' => $observacion,
        ]);
        $item->carrito->touch();
        return $item->load('producto');
    }
}
