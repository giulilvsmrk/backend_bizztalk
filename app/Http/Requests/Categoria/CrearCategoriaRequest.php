<?php

namespace App\Http\Requests\Categoria;

use Illuminate\Foundation\Http\FormRequest;

class CrearCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'id_negocio' => 'required|uuid|exists:negocio,id',
            'nombre'     => 'required|string|max:255',
        ];
    }
}
