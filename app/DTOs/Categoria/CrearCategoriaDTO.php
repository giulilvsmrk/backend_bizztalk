<?php

declare(strict_types=1);

namespace App\DTOs\Categoria;

final class CrearCategoriaDTO
{
    public function __construct(
        public readonly string $idNegocio,
        public readonly string $nombre
    ) {}
}
