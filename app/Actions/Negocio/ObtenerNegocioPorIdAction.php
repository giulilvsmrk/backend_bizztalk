<?php

declare(strict_types=1);

namespace App\Actions\Negocio;

use App\Models\Negocio;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class ObtenerNegocioPorIdAction
{
    /**
     * Busca un negocio por ID.
     * @throws ModelNotFoundException Si no existe.
     */
    public function ejecutar(string $id): Negocio
    {
        return Negocio::with(['sucursales' => function ($query) {
                $query->orderBy('fecha_creacion', 'asc')->limit(1);
            }])
            ->findOrFail($id);
    }
}
