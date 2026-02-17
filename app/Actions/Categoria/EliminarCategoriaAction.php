<?php

namespace App\Actions\Categoria;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EliminarCategoriaAction
{
    public function execute(int $id): void
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            throw new ModelNotFoundException("Categoría no encontrada.");
        }

        $categoria->delete();
    }
}
