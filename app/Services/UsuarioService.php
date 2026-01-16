<?php

namespace App\Services;

use App\Models\Usuario;
use MatanYadaev\EloquentSpatial\Objects\Point;

class UsuarioService
{
    public function getAll()
    {
        return Usuario::all();
    }
    public function create(array $data): Usuario
    {
        if (isset($data['ubicacion_actual']) && isset($data['ubicacion_actual']['lat'], $data['ubicacion_actual']['lng'])) {
            $data['ubicacion_actual'] = new Point(
                $data['ubicacion_actual']['lat'],
                $data['ubicacion_actual']['lng']
            );
        }

        return Usuario::create($data);
    }
    public function find(string $id): ?Usuario
    {
        return Usuario::findOrFail($id);
    }

    public function update(Usuario $usuario, array $data): Usuario
    {
        if (isset($data['ubicacion_actual']) && isset($data['ubicacion_actual']['lat'], $data['ubicacion_actual']['lng'])) {
            $data['ubicacion_actual'] = new Point(
                $data['ubicacion_actual']['lat'],
                $data['ubicacion_actual']['lng']
            );
        }

        $usuario->update($data);
        return $usuario;
    }
    public function delete(Usuario $usuario): void
    {
        $usuario->delete();
    }
}
