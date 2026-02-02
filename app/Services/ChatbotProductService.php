<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Inventario;
use App\Models\Negocio;
use App\Models\Sucursal;

class ChatbotProductService
{
    /**
     * Obtener productos disponibles y agotados del negocio con stock
     * 
     * @param Negocio $negocio
     * @return array
     */
    public function obtenerProductosConStock(Negocio $negocio): array
    {
        // Obtener productos activos del negocio
        $productos = $negocio->productos()
            ->where('activo', true)
            ->select(['id', 'nombre', 'descripcion', 'precio_base'])
            ->limit(50)
            ->get();

        if ($productos->isEmpty()) {
            return [
                'productosDisponibles' => [],
                'productosAgotados' => []
            ];
        }

        // Obtener sucursal del negocio para consultar stock
        $sucursal = Sucursal::where('id_negocio', $negocio->id)->first();
        $idSucursal = $sucursal?->id;

        // Mapear productos con su stock
        $productosConStock = $productos->map(function($producto) use ($idSucursal) {
            $inventario = null;
            if ($idSucursal) {
                $inventario = Inventario::where('id_sucursal', $idSucursal)
                    ->where('id_producto', $producto->id)
                    ->first();
            }

            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'precio' => "Bs. " . number_format((float)$producto->precio_base, 2, '.', ','),
                'stock' => [
                    'cantidad' => $inventario->cantidad ?? 0,
                    'agotado' => ($inventario->cantidad ?? 0) === 0
                ]
            ];
        })->values()->all();

        // Separar disponibles de agotados
        $productosDisponibles = array_filter(
            $productosConStock,
            fn($p) => $p['stock']['cantidad'] > 0
        );

        $productosAgotados = array_filter(
            $productosConStock,
            fn($p) => $p['stock']['cantidad'] === 0
        );

        return [
            'productosDisponibles' => array_values($productosDisponibles),
            'productosAgotados' => array_values($productosAgotados)
        ];
    }
}
