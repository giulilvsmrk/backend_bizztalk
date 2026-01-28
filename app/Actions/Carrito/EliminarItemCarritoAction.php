<?php
declare(strict_types=1);

namespace App\Actions\Carrito;

use App\Models\ItemCarrito;

class EliminarItemCarritoAction
{
    public function execute(int $idItem): void
    {
        $item = ItemCarrito::findOrFail($idItem);
        $item->carrito->touch();
        $item->delete();
    }
}
