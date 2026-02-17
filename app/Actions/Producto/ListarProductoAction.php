<?php

namespace App\Actions\Producto;

use App\Models\Producto;

class ListarProductoAction
{
    public function execute(): \Illuminate\Database\Eloquent\Collection
    {
        return Producto::with('registrosInventario')->get();
    }
}
