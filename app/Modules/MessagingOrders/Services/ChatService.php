<?php

declare(strict_types=1);

namespace App\Modules\MessagingOrders\Services;

use App\Models\Negocio;
use App\Modules\MessagingOrders\Models\Chat;
use App\Modules\MessagingOrders\Exceptions\AssistantConnectionException;
use App\Modules\MessagingOrders\Exceptions\AssistantGenerationException;
use App\Modules\MessagingOrders\Exceptions\NegocioNotFoundException;
use Illuminate\Support\Str;

/**
 * ChatService
 * 
 * Servicio orquestador del Asistente Virtual Conversacional.
 * Coordina la comunicación con la API del Asistente Virtual (máquina de estados).
 * Maneja:
 * - Conversaciones entre cliente y asistente
 * - Estado del carrito
 * - Creación de órdenes finales
 */
class ChatService
{
    private VirtualAssistantService $assistantService;

    public function __construct(
        VirtualAssistantService $assistantService
    ) {
        $this->assistantService = $assistantService;
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
            $respuestaBot = $this->spaceLLMService->generateResponse($prompt);

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
        } catch (AssistantConnectionException $e) {
            return [
                'exito' => false,
                'error' => 'Servicio Asistente no disponible',
                'codigo' => 'ASSISTANT_NO_DISPONIBLE'
            ];
        } catch (AssistantGenerationException $e) {
            return [
                'exito' => false,
                'error' => 'Error procesando mensaje',
                'codigo' => 'ERROR_PROCESAMIENTO'
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
            'metadata' => ['modelo' => 'tinyllama']
        ]);
    }

    public function obtenerEstadoSpaceLLM(): array
    {
        try {
            return [
                'conectado' => $this->spaceLLMService->verificarConexion(),
                'info' => $this->spaceLLMService->getInfo(),
            ];
        } catch (\Exception $e) {
            return [
                'conectado' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
