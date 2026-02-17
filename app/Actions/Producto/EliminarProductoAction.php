<?php

namespace App\Actions\Producto;

use App\Models\Producto;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EliminarProductoAction
{
    public function execute(string $id): void
    {
        $producto = Producto::find($id);

        if (!$producto) {
            throw new ModelNotFoundException("Producto no encontrado.");
        }

        $producto->delete();
    }
}
