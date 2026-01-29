<?php
declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemCarritoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'id_producto' => $this->id_producto,
            'cantidad' => $this->cantidad,
            'observacion' => $this->observacion,
            'precio_unitario' => $this->precio_unitario_estimado,
            'subtotal' => $this->subtotal_estimado,
            'producto' => [
                'nombre' => $this->producto->nombre ?? null,
                'imagen_url' => $this->producto->imagen_url ?? null,
            ],
        ];
    }
}
