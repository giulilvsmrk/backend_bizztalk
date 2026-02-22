<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use Illuminate\Http\Request;

readonly class RegistroUsuarioDTO
{
public function __construct(
    public string $nombres,
    public string $apellidos,
    public string $telefono,
    public string $password,
    public string $rol,
    public ?string $correo = null,
) {}

    public static function desdeRequest(Request $request): self
    {
        return new self(
            nombres: (string) $request->validated('nombres'),
            apellidos: (string) $request->validated('apellidos'),
            telefono: (string) $request->validated('telefono'),
            password: (string) $request->validated('password'),
            rol: (string) $request->validated('rol'), 
            correo: $request->validated('correo'),
        );
    }
}
