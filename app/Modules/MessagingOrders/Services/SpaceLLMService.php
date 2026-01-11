<?php

namespace App\Modules\MessagingOrders\Services;

use Illuminate\Support\Facades\Http;
use App\Modules\MessagingOrders\Exceptions\OllamaConnectionException;
use App\Modules\MessagingOrders\Exceptions\OllamaGenerationException;

/**
 * Servicio de integración con HuggingFace Space LLM
 * 
 * Se conecta a un Space deployado en HuggingFace que corre un modelo LLM.
 * Endpoint: https://huggingface.co/spaces/Saraqch10/Bizztalk
 */
class SpaceLLMService
{
    /**
     * URL del endpoint del Space
     */
    protected string $url;

    /**
     * Timeout para la solicitud (segundos)
     */
    protected int $timeout;

    /**
     * Temperatura para la generación de respuestas
     */
    protected float $temperature;

    public function __construct()
    {
        $this->url = config('space-llm.url');
        $this->timeout = config('space-llm.timeout');
        $this->temperature = config('space-llm.temperature');

        if (!$this->url) {
            throw new OllamaConnectionException('SPACE_LLM_URL no está configurada en .env');
        }
    }

    /**
     * Verificar conexión con el Space LLM
     * 
     * @return bool
     * @throws OllamaConnectionException
     */
    public function verificarConexion(): bool
    {
        try {
            $response = Http::timeout(10)
                ->post($this->url, [
                    'prompt' => 'Hola',
                ]);

            if (!$response->successful()) {
                throw new OllamaConnectionException('No se puede conectar al Space LLM.');
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('SpaceLLM Connection Error', ['error' => $e->getMessage()]);
            throw new OllamaConnectionException('Error de conexión con Space LLM: ' . $e->getMessage());
        }
    }

    /**
     * Generar respuesta usando el Space LLM
     * 
     * @param string $prompt El prompt completo
     * @return string La respuesta generada
     * @throws OllamaGenerationException
     * @throws OllamaConnectionException
     */
    public function generateResponse(string $prompt): string
    {
        try {
            \Log::info('SpaceLLMService: Iniciando request', [
                'url' => $this->url,
                'prompt_length' => strlen($prompt)
            ]);

            $response = Http::timeout($this->timeout)
                ->post($this->url, [
                    'prompt' => $prompt,
                ]);

            \Log::info('SpaceLLMService: Response recibida', [
                'status' => $response->status(),
                'body' => substr($response->body(), 0, 200)
            ]);

            if (!$response->successful()) {
                $errorMsg = $response->json('error') ?? $response->body();
                throw new OllamaGenerationException('Error del Space LLM: ' . json_encode($errorMsg));
            }

            $data = $response->json();
            
            // El Space devuelve un objeto con "response" o un array con "generated_text"
            $content = null;
            
            if (isset($data['response'])) {
                $content = $data['response'];
            } elseif (is_array($data) && count($data) > 0 && isset($data[0]['generated_text'])) {
                $content = $data[0]['generated_text'];
            }

            if (!$content) {
                throw new OllamaGenerationException('Respuesta vacía del Space LLM');
            }

            // Limpiar la respuesta: remover el prompt original si viene incluido
            $content = str_replace($prompt, '', $content);
            return trim($content);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new OllamaConnectionException('No se puede conectar al Space LLM: ' . $e->getMessage());
        } catch (\Exception $e) {
            if ($e instanceof OllamaGenerationException || $e instanceof OllamaConnectionException) {
                throw $e;
            }
            throw new OllamaGenerationException('Error al generar respuesta: ' . $e->getMessage());
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
            'temperature' => $this->temperature,
            'tipo' => 'HuggingFace Space LLM'
        ];
    }
}
