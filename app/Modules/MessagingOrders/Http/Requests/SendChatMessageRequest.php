<?php

declare(strict_types=1);

namespace App\Modules\MessagingOrders\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * SendChatMessageRequest
 * Valida los datos del mensaje antes de procesarlo
 */
class SendChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_id' => [
                'required',
                'string',
                'uuid',
                Rule::exists('negocio', 'id')->where('activo', true)
            ],
            'message' => [
                'required',
                'string',
                'min:1',
                'max:2000'
            ],
            'history_limit' => [
                'nullable',
                'integer',
                'min:0',
                'max:20'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'business_id.required' => 'El ID del negocio es requerido',
            'business_id.exists' => 'El negocio no existe',
            'message.required' => 'El mensaje es requerido',
            'message.max' => 'El mensaje es muy largo',
        ];
    }

    protected function prepareForValidation()
    {
        // Establecer valor por defecto para history_limit
        if (!$this->has('history_limit')) {
            $this->merge(['history_limit' => 5]);
        }
    }
}
