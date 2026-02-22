<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTOs\Auth\RegistroUsuarioDTO;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

final class RegistrarUsuarioAction
{
    public function ejecutar(RegistroUsuarioDTO $dto): Usuario
    {
        return DB::transaction(function () use ($dto) {
            $usuario = Usuario::create([
                'nombres' => $dto->nombres,
                'apellidos' => $dto->apellidos,
                'telefono' => $dto->telefono,
                'correo' => $dto->correo,
                'password_hash' => $dto->password,
                'activo' => true,
            ]);
            $rol = Rol::where('nombre', $dto->rol)->first();
            if ($rol) {
                $usuario->roles()->attach($rol->id);
            }
            

            return $usuario;
        });
    }
}
