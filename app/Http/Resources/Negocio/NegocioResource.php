<?php

declare(strict_types=1);

namespace App\Http\Resources\Negocio;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Recurso de API para la presentación de datos del Negocio.
 * Transforma los modelos Eloquent en un formato JSON estricto para el cliente.
 */
class NegocioResource extends JsonResource
{
    /**
     * Transformar el recurso en un array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'nit' => $this->nit,
            'descripcion' => $this->descripcion,
            'logo_url' => $this->logotipo_url, // Puede ser null
            'activo' => (bool) $this->activo,
            'fecha_registro' => $this->created_at?->toIso8601String(),

            // Relación Condicional: Solo se muestra si se cargó con ->load('sucursales')
            // Esto evita consultas N+1 accidentales si olvidamos cargar la relación.
            'sucursales' => $this->whenLoaded('sucursales', function () {
                return $this->sucursales->map(function ($sucursal) {
                    return [
                        'id' => $sucursal->id,
                        'nombre' => $sucursal->nombre_sucursal,
                        'direccion' => $sucursal->direccion_texto,
                        // Extracción manual de coordenadas del objeto Point espacial
                        'ubicacion' => [
                            'lat' => $sucursal->ubicacion_gps?->latitude,
                            'lng' => $sucursal->ubicacion_gps?->longitude,
                        ],
                        'activo' => (bool) $sucursal->activo,
                    ];
                });
            }),
        ];
    }
}
