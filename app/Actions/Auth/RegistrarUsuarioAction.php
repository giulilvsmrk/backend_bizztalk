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
            $rolCliente = Rol::where('nombre', 'cliente')->first();

            if ($rolCliente) {
                $usuario->roles()->attach($rolCliente->id);
            }

            return $usuario;
        });
    }
}
