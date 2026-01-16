<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

class LoginDTO
{
    public function __construct(
        public readonly string $correo,
        public readonly string $password,
        public readonly ?string $deviceName
    ) {}
}
