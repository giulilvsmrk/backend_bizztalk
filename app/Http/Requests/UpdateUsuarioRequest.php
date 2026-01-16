<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $usuarioId = $this->route('usuario'); 
        return [
            'nombres' => 'sometimes|required|string|max:255',
            'apellidos' => 'sometimes|required|string|max:255',
            'correo' => "sometimes|required|email|unique:usuario,correo,{$usuarioId},id",
            'password_hash' => 'sometimes|required|string|min:6',
            'telefono' => "sometimes|nullable|string|unique:usuario,telefono,{$usuarioId},id",
            'ubicacion_actual' => 'nullable|array',
            'activo' => 'boolean',
        ];
    }
}
