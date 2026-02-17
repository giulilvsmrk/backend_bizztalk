<?php

namespace App\Actions\Categoria;

use App\Models\Categoria;
use App\DTOs\Categoria\ActualizarCategoriaDTO;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ActualizarCategoriaAction
{
    public function execute(int $id, ActualizarCategoriaDTO $dto): Categoria
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            throw new ModelNotFoundException("Categoría no encontrada.");
        }

        $categoria->update([
            'nombre' => $dto->nombre ?? $categoria->nombre,
        ]);

        return $categoria;
    }
}
