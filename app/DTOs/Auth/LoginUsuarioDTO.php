<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use Illuminate\Http\Request;

readonly class LoginUsuarioDTO
{
    public function __construct(
        public string $identificador,
        public string $password,
        public string $dispositivo
    ) {}

    public static function desdeRequest(Request $request): self
    {
        return new self(
            identificador: (string) $request->validated('identificador'),
            password: (string) $request->validated('password'),
            dispositivo: (string) ($request->header('User-Agent') ?? 'Dispositivo Desconocido'),
        );
    }
}
