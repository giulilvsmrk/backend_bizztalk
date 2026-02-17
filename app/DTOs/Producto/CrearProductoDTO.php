<?php

namespace App\DTOs\Producto;

class CrearProductoDTO
{
    public function __construct(
        public string $idNegocio,
        public ?int $idCategoria,
        public string $nombre,
        public ?string $descripcion,
        public float $precioBase,
        public int $cantidad,
        public ?string $imagenUrl 
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            idNegocio: $data['id_negocio'],
            idCategoria: $data['id_categoria'] ?? null,
            nombre: $data['nombre'],
            descripcion: $data['descripcion'] ?? null,
            precioBase: $data['precio_base'],
            cantidad: $data['cantidad'],
            imagenUrl: $data['imagen_url'] ?? null 
        );
    }
}