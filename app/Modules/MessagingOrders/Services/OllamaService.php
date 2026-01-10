<?php

declare(strict_types=1);

namespace App\Modules\MessagingOrders\Services;

use Illuminate\Support\Facades\Http;
use App\Modules\MessagingOrders\Exceptions\OllamaConnectionException;
use App\Modules\MessagingOrders\Exceptions\OllamaGenerationException;

/**
 * OllamaService
 * 
 * Servicio para conectar con Ollama y generar respuestas
 * usando el modelo Mistral 7B.
 */
class OllamaService
{
    private string $ollamaUrl;
    private string $model;
    private int $timeout;
    private float $temperature;

    public function __construct()
    {
        $this->ollamaUrl = config('ollama.url', env('OLLAMA_URL', 'http://localhost:11434'));
        $this->model = config('ollama.model', env('OLLAMA_MODEL', 'neural-chat'));
        $this->timeout = config('ollama.timeout', (int)env('OLLAMA_TIMEOUT', 120)); // Aumentado a 120 segundos
        $this->temperature = config('ollama.temperature', (float)env('OLLAMA_TEMPERATURE', 0.7));
    }

    /**
     * Generar respuesta usando Ollama
     * 
     * @throws OllamaConnectionException
     * @throws OllamaGenerationException
     */
    public function generateResponse(string $prompt): string
    {
        try {
            $this->verificarConexion();

            $payload = [
                'model' => $this->model,
                'prompt' => $prompt,
                'stream' => false,
                'temperature' => $this->temperature,
            ];

            $response = Http::timeout($this->timeout)
                           ->post("{$this->ollamaUrl}/api/generate", $payload);

            if (!$response->successful()) {
                throw new OllamaGenerationException(
                    "Error: {$response->status()} - {$response->body()}"
                );
            }

            $responseData = $response->json();

            if (!isset($responseData['response'])) {
                throw new OllamaGenerationException('Respuesta inválida de Ollama');
            }

            return trim($responseData['response']);

        } catch (OllamaConnectionException | OllamaGenerationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new OllamaGenerationException("Error: {$e->getMessage()}");
        }
    }

    /**
     * Verificar que Ollama está disponible
     * 
     * @throws OllamaConnectionException
     */
    private function verificarConexion(): void
    {
        try {
            $response = Http::timeout(5)->get("{$this->ollamaUrl}/api/tags");

            if (!$response->successful()) {
                throw new OllamaConnectionException(
                    "Ollama no disponible. Status: {$response->status()}"
                );
            }

        } catch (\Exception $e) {
            if ($e instanceof OllamaConnectionException) {
                throw $e;
            }
            throw new OllamaConnectionException(
                "No se puede conectar a Ollama en {$this->ollamaUrl}"
            );
        }
    }

    /**
     * Obtener información de Ollama (debug)
     */
    public function obtenerInfo(): array
    {
        try {
            $this->verificarConexion();
            return [
                'estado' => 'conectado',
                'url' => $this->ollamaUrl,
                'modelo' => $this->model,
            ];
        } catch (OllamaConnectionException $e) {
            return [
                'estado' => 'desconectado',
                'error' => $e->getMessage(),
            ];
        }
    }
}
