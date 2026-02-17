<?php

namespace App\Services;

use App\Models\Producto;
use Illuminate\Support\Facades\DB;

class ProductoService
{
    public function crearProducto(array $data): Producto
    {
        return DB::transaction(function () use ($data) {

            $producto = Producto::create([
                'id_negocio'   => $data['id_negocio'],
                'id_categoria' => $data['id_categoria'] ?? null,
                'nombre'       => $data['nombre'],
                'descripcion'  => $data['descripcion'] ?? null,
                'precio_base'  => $data['precio_base'],
                'imagen_url'   => $data['imagen_url'] ?? null, // 👈 AQUÍ
                'activo'       => true,
            ]);

            $producto->registrosInventario()->create([
                'cantidad'     => $data['cantidad'],
                'precio_local' => $data['precio_base'],
                'activo'       => true,
            ]);

            return $producto->load('registrosInventario');
        });
    }
}