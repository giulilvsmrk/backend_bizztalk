<?php

declare(strict_types=1);

namespace App\Http\Requests\Negocio;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistroNegocioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'negocio' => ['required', 'array'],
            'negocio.nombre' => ['required', 'string', 'max:255', 'min:3'],
            'negocio.nit' => [
                'required',
                'string',
                'max:50',
                'alpha_num',
                Rule::unique('negocio', 'nit')
            ],
            'negocio.descripcion' => ['nullable', 'string', 'max:1000'],
            'negocio.logo_url' => ['nullable', 'url', 'max:500'],
            'negocio.direccion' => ['required', 'string', 'max:500'],
            'negocio.imagen_portada' => ['nullable', 'url', 'max:500'],
            'negocio.ubicacion' => ['required', 'array'],
            'negocio.ubicacion.point' => ['required', 'array'],
            'negocio.ubicacion.point.lat' => ['required', 'numeric', 'between:-90,90'],
            'negocio.ubicacion.point.lng' => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    public function messages(): array
    {
        return [
            'negocio.nit.unique' => 'El NIT ya está registrado.',
            'negocio.ubicacion.point.lat.required' => 'La latitud es obligatoria.',
            'negocio.logo_url.url' => 'El logo debe ser una URL válida.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('negocio.nit')) {
            $data = $this->input('negocio');
            $data['nit'] = strtoupper(trim((string)$data['nit']));
            $this->merge(['negocio' => $data]);
        }
    }
}
