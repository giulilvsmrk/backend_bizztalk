<?php

declare(strict_types=1);

namespace App\Modules\MessagingOrders\Services;

use App\Models\Negocio;
use App\Modules\MessagingOrders\Models\Chat;
use App\Modules\MessagingOrders\Exceptions\OllamaConnectionException;
use App\Modules\MessagingOrders\Exceptions\OllamaGenerationException;
use App\Modules\MessagingOrders\Exceptions\NegocioNotFoundException;

/**
 * ChatService
 * 
 * Servicio orquestador que coordina todo el flujo del chat.
 * Conecta OllamaService y PromptBuilder.
 */
class ChatService
{
    private OllamaService $ollamaService;
    private PromptBuilder $promptBuilder;

    public function __construct(
        OllamaService $ollamaService,
        PromptBuilder $promptBuilder
    ) {
        $this->ollamaService = $ollamaService;
        $this->promptBuilder = $promptBuilder;
    }

    /**
     * Procesar mensaje y generar respuesta
     */
    public function procesarMensaje(
        string $idNegocio,
        string $mensajeUsuario,
        int $limitHistorial = 5
    ): array {
        try {
            // Obtener negocio
            $negocio = $this->obtenerNegocio($idNegocio);

            // Obtener historial
            $historial = $this->obtenerHistorial($idNegocio, $limitHistorial);
            $historialFormateado = $this->promptBuilder->formatearHistorial($historial);

            // Construir prompt
            $prompt = $this->promptBuilder->construirPromptCompleto(
                negocio: $negocio,
                mensajeUsuario: $mensajeUsuario,
                historial: $historialFormateado
            );

            // Generar respuesta
            $respuestaBot = $this->ollamaService->generateResponse($prompt);

            // Guardar en BD
            $chat = $this->guardarChat(
                idNegocio: $idNegocio,
                mensajeUsuario: $mensajeUsuario,
                respuestaBot: $respuestaBot
            );

            return [
                'exito' => true,
                'datos' => [
                    'respuesta' => $respuestaBot,
                    'id_chat' => $chat->id,
                    'timestamp' => $chat->fecha_creacion->toIso8601String(),
                ]
            ];

        } catch (NegocioNotFoundException $e) {
            return [
                'exito' => false,
                'error' => $e->getMessage(),
                'codigo' => 'NEGOCIO_NO_ENCONTRADO'
            ];
        } catch (OllamaConnectionException $e) {
            return [
                'exito' => false,
                'error' => 'Servicio IA no disponible',
                'codigo' => 'OLLAMA_NO_DISPONIBLE'
            ];
        } catch (OllamaGenerationException $e) {
            return [
                'exito' => false,
                'error' => 'Error generando respuesta',
                'codigo' => 'ERROR_GENERACION'
            ];
        } catch (\Exception $e) {
            \Log::error('ChatService error', ['error' => $e->getMessage()]);
            return [
                'exito' => false,
                'error' => 'Error interno',
                'codigo' => 'ERROR_INTERNO'
            ];
        }
    }

    private function obtenerNegocio(string $idNegocio): Negocio
    {
        $negocio = Negocio::with('productos')->find($idNegocio);

        if (!$negocio) {
            throw new NegocioNotFoundException("Negocio no encontrado: {$idNegocio}");
        }

        return $negocio;
    }

    private function obtenerHistorial(string $idNegocio, int $limite): \Illuminate\Database\Eloquent\Collection
    {
        return Chat::delNegocio($idNegocio)
                   ->activos()
                   ->orderBy('fecha_creacion', 'desc')
                   ->limit($limite)
                   ->get()
                   ->reverse();
    }

    private function guardarChat(
        string $idNegocio,
        string $mensajeUsuario,
        string $respuestaBot
    ): Chat {
        return Chat::create([
            'id_negocio' => $idNegocio,
            'mensaje_usuario' => $mensajeUsuario,
            'respuesta_bot' => $respuestaBot,
            'activo' => true,
            'metadata' => ['modelo' => 'mistral']
        ]);
    }

    public function obtenerEstadoOllama(): array
    {
        return $this->ollamaService->obtenerInfo();
    }
}
