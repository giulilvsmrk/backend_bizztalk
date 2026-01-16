<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\LoginUsuarioAction;
use App\Actions\Auth\RegistrarUsuarioAction;
use App\DTOs\Auth\LoginUsuarioDTO;
use App\DTOs\Auth\RegistroUsuarioDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegistroRequest;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function registro(RegistroRequest $request, RegistrarUsuarioAction $action): JsonResponse
    {
        $dto = RegistroUsuarioDTO::desdeRequest($request);
        $usuario = $action->ejecutar($dto);

        return response()->json([
            'message' => 'Usuario registrado exitosamente.',
            'data' => [
                'id' => $usuario->id,
                'nombres' => $usuario->nombres,
                'correo' => $usuario->correo,
            ]
        ], 201);
    }

    public function login(LoginRequest $request, LoginUsuarioAction $action): JsonResponse
    {
        $dto = LoginUsuarioDTO::desdeRequest($request);
        $resultado = $action->ejecutar($dto);

        $usuario = $resultado['usuario'];

        return response()->json([
            'message' => 'Bienvenido.',
            'token' => $resultado['token'],
            'token_type' => 'Bearer',
            'usuario' => [
                'id' => $usuario->id,
                'nombres' => $usuario->nombres,
                'apellidos' => $usuario->apellidos,
                'email' => $usuario->correo,
                'roles' => $usuario->roles->pluck('nombre'),
            ]
        ]);
    }
}
