<?php
declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CarritoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'id_usuario' => $this->id_usuario,
            'id_sucursal_activa' => $this->id_sucursal_activa,
            'subtotal' => $this->calcularSubtotal(),
            'items' => ItemCarritoResource::collection($this->items),
        ];
    }
}
