<?php

namespace App\Actions\Producto;

use App\DTOs\Producto\CrearProductoDTO;
use App\Services\ProductoService;
use App\Models\Producto;

class CrearProductoAction
{
    public function __construct(
        protected ProductoService $service
    ) {}

    public function execute(CrearProductoDTO $dto): Producto
    {
        return $this->service->crearProducto([
            'id_negocio'   => $dto->idNegocio,
            'id_categoria' => $dto->idCategoria,
            'nombre'       => $dto->nombre,
            'descripcion'  => $dto->descripcion,
            'precio_base'  => $dto->precioBase,
            'cantidad'     => $dto->cantidad,
            'imagen_url'   => $dto->imagenUrl, 
        ]);
    }
}