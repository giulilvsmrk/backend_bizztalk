<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\DTOs\Auth\LoginDTO;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(LoginDTO $dto): array
    {
        $usuario = Usuario::where('correo', $dto->correo)->first();

        if (! $usuario || ! Hash::check($dto->password, $usuario->password_hash)) {
            throw ValidationException::withMessages([
                'correo' => ['Credenciales incorrectas'],
            ]);
        }

        if (! $usuario->activo) {
            abort(403, 'Usuario inactivo');
        }

        // Política: 1 token activo por usuario
        $usuario->tokens()->delete();

        $token = $usuario->createToken(
            $dto->deviceName ?? 'api-token'
        )->plainTextToken;
        $roles = $usuario->roles()->pluck('nombre');
        return [
            'token' => $token,
            'token_type' => 'Bearer',
            'usuario' => [
                'id' => $usuario->id,
                'nombres' => $usuario->nombres,
                'apellidos' => $usuario->apellidos,
                'correo' => $usuario->correo,
                'roles' => $roles,
            ],
        ];
    }

    public function logout(Usuario $usuario): void
    {
        $usuario->currentAccessToken()->delete();
    }
}
