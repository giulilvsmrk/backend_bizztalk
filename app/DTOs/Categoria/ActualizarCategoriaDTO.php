<?php

declare(strict_types=1);

namespace App\DTOs\Categoria;

final class ActualizarCategoriaDTO
{
    public function __construct(
        public readonly ?string $nombre = null
    ) {}
}
