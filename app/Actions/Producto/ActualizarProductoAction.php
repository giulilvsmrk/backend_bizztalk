<?php

namespace App\Actions\Producto;

use App\Models\Producto;
use App\DTOs\Producto\ActualizarProductoDTO;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class ActualizarProductoAction
{
    public function execute(string $id, ActualizarProductoDTO $dto): Producto
    {
        $producto = Producto::find($id);

        if (!$producto) {
            throw new ModelNotFoundException("Producto no encontrado.");
        }

        DB::transaction(function () use ($producto, $dto) {
           
            $producto->update([
                'id_categoria' => $dto->idCategoria ?? $producto->id_categoria,
                'nombre'       => $dto->nombre ?? $producto->nombre,
                'descripcion'  => $dto->descripcion ?? $producto->descripcion,
                'precio_base'  => $dto->precioBase ?? $producto->precio_base,
                'imagen_url'   => $dto->imagenUrl ?? $producto->imagen_url,
            ]);

            
            if ($dto->cantidad !== null) {
                $producto->registrosInventario()->updateOrCreate(
                    ['id_producto' => $producto->id], 
                    [
                        'cantidad'     => $dto->cantidad,
                        'precio_local' => $dto->precioBase ?? $producto->precio_base,
                        'activo'       => true,
                    ]
                );
            }
        });

        return $producto->load('registrosInventario');
    }
}
