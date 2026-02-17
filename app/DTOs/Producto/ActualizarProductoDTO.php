<?php

namespace App\DTOs\Producto;

class ActualizarProductoDTO
{
    public function __construct(
        public ?int $idCategoria,
        public ?string $nombre,
        public ?string $descripcion,
        public ?float $precioBase,
        public ?int $cantidad,
        public ?string $imagenUrl
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            idCategoria: $data['id_categoria'] ?? null,
            nombre: $data['nombre'] ?? null,
            descripcion: $data['descripcion'] ?? null,
            precioBase: $data['precio_base'] ?? null,
            cantidad: $data['cantidad'] ?? null,
            imagenUrl: $data['imagen_url'] ?? null
        );
    }
}
