<?php

declare(strict_types=1);

namespace App\DTOs\Negocio;

use Illuminate\Http\Request;

readonly class RegistroNegocioDTO
{
    public function __construct(
        public string $nombre,
        public string $nit,
        public ?string $descripcion,
        public ?string $logoUrl,
        public string $direccionTexto,
        public float $latitud,
        public float $longitud,
        public ?string $imagenPortada,
    ) {}

    public static function desdeRequest(Request $request): self
    {
        $data = $request->input('negocio');

        return new self(
            nombre: (string) $data['nombre'],
            nit: (string) $data['nit'],
            descripcion: $data['descripcion'] ?? null,
            logoUrl: $data['logo_url'] ?? null,
            direccionTexto: (string) $data['direccion'],
            latitud: (float) $data['ubicacion']['point']['lat'],
            longitud: (float) $data['ubicacion']['point']['lng'],

            imagenPortada: $data['imagen_portada'] ?? null,
        );
    }
}
