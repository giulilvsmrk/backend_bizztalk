<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgregarItemCarritoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_usuario' => 'required|uuid|exists:usuario,id',
            'id_producto' => 'required|uuid|exists:producto,id',
            'cantidad' => 'required|integer|min:1',
            'observacion' => 'nullable|string|max:255',
        ];
    }
}
