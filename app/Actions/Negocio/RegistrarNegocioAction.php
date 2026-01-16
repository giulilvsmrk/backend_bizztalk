<?php

declare(strict_types=1);

namespace App\Actions\Negocio;

use App\DTOs\Negocio\RegistroNegocioDTO;
use App\Models\Negocio;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use App\Values\PostgresPoint;

final class RegistrarNegocioAction
{
    public function ejecutar(RegistroNegocioDTO $dto, Usuario $propietario): Negocio
    {
        return DB::transaction(function () use ($dto, $propietario) {

            $rolDueno = Rol::where('nombre', 'dueno')->firstOrFail();
            $propietario->roles()->syncWithoutDetaching([$rolDueno->id]);
            $negocio = Negocio::create([
                'id_propietario' => $propietario->id,
                'nombre' => $dto->nombre,
                'nit' => $dto->nit,
                'descripcion' => $dto->descripcion,
                'logotipo_url' => $dto->logoUrl,
                'activo' => true,
            ]);

            Sucursal::create([
                'id_negocio' => $negocio->id,
                'nombre_sucursal' => $dto->nombre,
                'direccion_texto' => $dto->direccionTexto,
                'ubicacion_gps' => new PostgresPoint($dto->latitud, $dto->longitud),
                'imagen_portada_url' => $dto->imagenPortada,
                'activo' => true,
            ]);
            return $negocio->load('sucursales');
        });
    }
}
