<?php

declare(strict_types=1);

namespace App\Modules\MessagingOrders\Services;

use Illuminate\Support\Facades\Http;
use App\Modules\MessagingOrders\Exceptions\AssistantConnectionException;
use App\Modules\MessagingOrders\Exceptions\AssistantGenerationException;

/**
 * VirtualAssistantService
 * 
 * Integración con API del Asistente Virtual Determinístico
 * Ubicado en: https://huggingface.co/spaces/Saraqch10/Bizztalk/api/chat
 * 
 * Este servicio usa una máquina de estados (sin LLM) para procesar
 * pedidos de forma determinística y controlada.
 */
class VirtualAssistantService
{
    protected string $url;
    protected int $timeout;

    public function __construct()
    {
        $this->url = config('virtual-assistant.url') ?? 'https://huggingface.co/spaces/Saraqch10/Bizztalk/api/chat';
        $this->timeout = (int) (config('virtual-assistant.timeout') ?? 30);

        if (!$this->url) {
            throw new AssistantConnectionException('VIRTUAL_ASSISTANT_URL no configurada');
        }
    }

    /**
     * Procesar mensaje del usuario a través del asistente virtual
     * 
     * @param array $storesData Datos de tiendas y productos: {store_key: {name, products}}
     * @param string $userPrompt Mensaje del usuario
     * @param string $sessionId ID único de la sesión
     * @param string $systemPrompt Instrucción del sistema (default)
     * 
     * @return array Respuesta del asistente con estado y carrito
     * @throws OllamaConnectionException
     * @throws OllamaGenerationException
     */
    public function processMessage(
        array $storesData,
        string $userPrompt,
        string $sessionId,
        string $systemPrompt = "Eres un asistente de pedidos amable y profesional."
    ): array {
        try {
            \Log::info('VirtualAssistantService: Enviando mensaje', [
                'session_id' => $sessionId,
                'user_prompt_length' => strlen($userPrompt),
                'stores_count' => count($storesData)
            ]);

            // Construir payload
            $payload = [
                'user_prompt' => $userPrompt,
                'system_prompt' => $systemPrompt,
                'stores' => $storesData,
                'session_id' => $sessionId,
                'temperature' => 0.7,
                'max_tokens' => 256
            ];

            // Realizar request
            $response = Http::timeout($this->timeout)
                ->post($this->url, $payload);

            \Log::info('VirtualAssistantService: Response recibida', [
                'status' => $response->status(),
                'session_id' => $sessionId
            ]);

            if (!$response->successful()) {
                throw new AssistantConnectionException(
                    'Error en API del Asistente Virtual: ' . $response->status()
                );
            }

            $data = $response->json();

            if (!isset($data['success']) || !$data['success']) {
                throw new AssistantGenerationException(
                    'Asistente retornó error: ' . ($data['error'] ?? 'Desconocido')
                );
            }

            return $data;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new AssistantConnectionException(
                'No se puede conectar al Asistente Virtual: ' . $e->getMessage()
            );
        } catch (\Exception $e) {
            if ($e instanceof AssistantGenerationException || $e instanceof AssistantConnectionException) {
                throw $e;
            }
            throw new AssistantGenerationException('Error en Asistente Virtual: ' . $e->getMessage());
        }
    }

    /**
     * Obtener información del servicio
     */
    public function getInfo(): array
    {
        return [
            'url' => $this->url,
            'timeout' => $this->timeout,
            'type' => 'Virtual Assistant - Deterministic State Machine'
        ];
    }
}
