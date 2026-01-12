<?php

declare(strict_types=1);

namespace App\Modules\MessagingOrders\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConfirmarPedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'session_id' => ['required', 'string', 'starts_with:session_'],
            'business_id' => ['required', 'string', 'uuid'],
            'carrito' => ['required', 'array', 'min:1'],
            'carrito.*.product' => ['required', 'string'],
            'carrito.*.quantity' => ['required', 'integer', 'min:1'],
            'carrito.*.price' => ['required', 'numeric', 'min:0'],
            'carrito.*.subtotal' => ['required', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0']
        ];
    }

    public function messages(): array
    {
        return [
            'session_id.required' => 'Session ID es requerido',
            'business_id.required' => 'Business ID es requerido',
            'carrito.required' => 'Carrito no puede estar vacío',
            'carrito.min' => 'El carrito debe tener al menos 1 producto',
            'total.required' => 'Total del pedido es requerido'
        ];
    }
}
