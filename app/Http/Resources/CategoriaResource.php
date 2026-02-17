<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoriaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'id_negocio' => $this->id_negocio,
            'nombre'     => $this->nombre,
            'productos'  => $this->whenLoaded('productos'), // opcional
        ];
    }
}
