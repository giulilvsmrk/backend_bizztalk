<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'nombre'      => $this->nombre,
            'descripcion' => $this->descripcion,
            'precio'      => $this->precio_base,
            'activo'      => $this->activo,
            'inventario'  => $this->registrosInventario->map(function ($item) {
                return [
                    
                    'cantidad'    => $item->cantidad,
                    'precio_local'=> $item->precio_local,
                ];
            }),
            'fecha_creacion' => $this->fecha_creacion,
        ];
    }
}