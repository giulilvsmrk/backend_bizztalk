<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTOs\Auth\LoginUsuarioDTO;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class LoginUsuarioAction
{
    /**
     * @return array{usuario: Usuario, token: string}
     */
    public function ejecutar(LoginUsuarioDTO $dto): array
    {
        // 1. Buscar usuario por Correo O Teléfono
        $usuario = Usuario::where('correo', $dto->identificador)
            ->orWhere('telefono', $dto->identificador)
            ->first();

        // 2. Validar Existencia y Contraseña
        // Hash::check compara el texto plano con el hash guardado en BD.
        if (! $usuario || ! Hash::check($dto->password, $usuario->password_hash)) {
            throw ValidationException::withMessages([
                'identificador' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // 3. Validar Estado
        if (! $usuario->activo) {
             throw ValidationException::withMessages([
                'identificador' => ['Tu cuenta está desactivada.'],
            ]);
        }

        // 4. Generar Token (Sanctum)
        $token = $usuario->createToken($dto->dispositivo)->plainTextToken;

        // 5. Cargar Roles (Eager Loading) para devolverlos al frontend
        $usuario->load('roles');

        return [
            'usuario' => $usuario,
            'token' => $token,
        ];
    }
}
