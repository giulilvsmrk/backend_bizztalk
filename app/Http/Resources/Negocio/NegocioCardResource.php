<?php

declare(strict_types=1);

namespace App\Http\Resources\Negocio;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NegocioCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Buscamos la primera sucursal (matriz) para robarle la foto de portada
        // Usamos first() porque en la Action ya nos encargamos de cargarla eficientemente.
        $sucursalPrincipal = $this->sucursales->first();

        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'nit' => $this->nit,
            'logo_url' => $this->logotipo_url,
            // Si hay sucursal, usamos su portada; si no, null.
            'imagen_portada' => $sucursalPrincipal?->imagen_portada_url,
            'activo' => (bool) $this->activo,
            'fecha_creacion' => $this->created_at?->toIso8601String(),
        ];
    }
}
