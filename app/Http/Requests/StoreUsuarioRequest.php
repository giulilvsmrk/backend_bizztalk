<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'correo' => 'required|email|unique:usuario,correo',
            'password_hash' => 'required|string|min:6',
            'telefono' => 'nullable|string|unique:usuario,telefono',
            'ubicacion_actual' => 'nullable|array', 
            'activo' => 'boolean',
        ];
    }
}