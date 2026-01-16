<?php

declare(strict_types=1);

namespace App\Http\Resources\Negocio;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NegocioDetalleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $sucursalPrincipal = $this->sucursales->first();

        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'nit' => $this->nit,
            'descripcion' => $this->descripcion,
            'logotipo_url' => $this->logotipo_url,
            'imagen_portada' => $sucursalPrincipal?->imagen_portada_url,
            'activo' => (bool) $this->activo,
            'fecha_creacion' => $this->created_at?->toIso8601String(),
            // Usamos updated_at si existe, sino created_at
            'fecha_actualizacion' => ($this->updated_at ?? $this->created_at)?->toIso8601String(),
        ];
    }
}
