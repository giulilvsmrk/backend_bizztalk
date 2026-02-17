<?php

namespace App\Actions\Producto;

use App\Models\Producto;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MostrarProductoAction
{
    public function execute(string $id): Producto
    {
        $producto = Producto::with('registrosInventario')->find($id);

        if (!$producto) {
            throw new ModelNotFoundException("Producto no encontrado.");
        }

        return $producto;
    }
}
