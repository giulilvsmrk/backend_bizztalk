<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegistroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'telefono' => ['required', 'string', 'max:20', 'unique:usuario,telefono'],
            'correo' => ['nullable', 'email', 'max:100', 'unique:usuario,correo'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'rol' => ['required', 'in:cliente,dueno'], // 👈 aquí
        ];
    }

    public function messages(): array
    {
        return [
            'telefono.unique' => 'Este número de teléfono ya está registrado.',
            'correo.unique' => 'Este correo ya está registrado.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }
}
