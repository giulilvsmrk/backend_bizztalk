<?php

namespace App\Http\Requests\Producto;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'id_negocio'   => ['required', 'uuid', 'exists:negocio,id'],
            'id_categoria' => ['nullable', 'exists:categoria,id'],
            'nombre'       => ['required', 'string', 'max:255'],
            'descripcion'  => ['nullable', 'string'],
            'precio_base'  => ['required', 'numeric', 'min:0'],
            'cantidad'     => ['required', 'integer', 'min:0'],
            'imagen_url' => 'nullable|url'
        ];
    }

    public function messages(): array
    {
        return [
            'id_negocio.exists' => 'El negocio no existe.',
            'sucursal_id.exists' => 'La sucursal no existe.',
        ];
    }
}