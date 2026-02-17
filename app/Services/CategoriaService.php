<?php

namespace App\Services;

use App\Actions\Categoria\CrearCategoriaAction;
use App\Actions\Categoria\ActualizarCategoriaAction;
use App\Actions\Categoria\EliminarCategoriaAction;
use App\DTOs\Categoria\CrearCategoriaDTO;
use App\DTOs\Categoria\ActualizarCategoriaDTO;
use App\Models\Categoria;

class CategoriaService
{
    public function __construct(
        private CrearCategoriaAction $crearAction,
        private ActualizarCategoriaAction $actualizarAction,
        private EliminarCategoriaAction $eliminarAction
    ) {}

    public function crear(array $data): Categoria
    {
        $dto = new CrearCategoriaDTO(
            idNegocio: $data['id_negocio'],
            nombre: $data['nombre']
        );

        return $this->crearAction->execute($dto);
    }

    public function actualizar(int $id, array $data): Categoria
    {
        $dto = new ActualizarCategoriaDTO(
            nombre: $data['nombre'] ?? null
        );

        return $this->actualizarAction->execute($id, $dto);
    }

    public function eliminar(int $id): void
    {
        $this->eliminarAction->execute($id);
    }
}
