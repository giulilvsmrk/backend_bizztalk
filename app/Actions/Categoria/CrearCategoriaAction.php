<?php

namespace App\Actions\Categoria;

use App\Models\Categoria;
use App\DTOs\Categoria\CrearCategoriaDTO;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CrearCategoriaAction
{
    public function execute(CrearCategoriaDTO $dto): Categoria
    {
        return Categoria::create([
            'id_negocio' => $dto->idNegocio,
            'nombre'     => $dto->nombre,
        ]);
    }
}
