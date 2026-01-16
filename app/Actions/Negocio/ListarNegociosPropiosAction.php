<?php

declare(strict_types=1);

namespace App\Actions\Negocio;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Collection;

final class ListarNegociosPropiosAction
{
    public function ejecutar(Usuario $propietario): Collection
    {
        // Obtenemos los negocios del usuario y PRE-CARGAMOS (Eager Load)
        // solo la sucursal más antigua para obtener la imagen de portada
        // sin matar la base de datos trayendo todo.
        return $propietario->negociosPropios()
            ->with(['sucursales' => function ($query) {
                $query->orderBy('fecha_creacion', 'asc')->limit(1);
            }])
            ->orderByDesc('fecha_creacion')
            ->get();
    }
}
