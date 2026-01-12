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
            // Generar session_id
            $sessionId = 'session_' . Str::uuid();

            // Obtener negocios y productos de BD
            $storesData = $this->construirDatosNegocios();
            
            if (empty($storesData)) {
                throw new NegocioNotFoundException("No hay negocios disponibles");
            }

            // Procesar mensaje con Asistente Virtual
            $respuestaAsistente = $this->assistantService->processMessage(
                storesData: $storesData,
                userPrompt: $mensajeUsuario,
                sessionId: $sessionId,
                systemPrompt: "Eres un asistente de pedidos amable y profesional."
            );

            // Guardar en BD
            $chat = $this->guardarChat(
                idNegocio: $idNegocio,
                mensajeUsuario: $mensajeUsuario,
                respuestaBot: $respuestaAsistente['response'],
                sessionId: $sessionId
            );

            return [
                'exito' => true,
                'datos' => [
                    'respuesta' => $respuestaAsistente['response'],
                    'id_chat' => $chat->id,
                    'timestamp' => $chat->fecha_creacion->toIso8601String(),
                    'estado' => $respuestaAsistente['current_state'] ?? null,
                    'carrito' => $respuestaAsistente['cart'] ?? [],
                    'session_id' => $sessionId
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

    private function construirDatosNegocios(): array
    {
        $negocios = Negocio::with('productos')->get();
        
        $storesData = [];
        foreach ($negocios as $negocio) {
            $productos = [];
            foreach ($negocio->productos as $producto) {
                $productos[$producto->id] = [
                    'name' => $producto->nombre,
                    'price' => (float) $producto->precio
                ];
            }
            
            $storesData[strtolower(str_replace(' ', '_', $negocio->nombre))] = [
                'name' => $negocio->nombre,
                'products' => $productos
            ];
        }
        
        return $storesData;
    }


    private function guardarChat(
        string $idNegocio,
        string $mensajeUsuario,
        string $respuestaBot,
        string $sessionId
    ): Chat {
        return Chat::create([
            'id_negocio' => $idNegocio,
            'mensaje_usuario' => $mensajeUsuario,
            'respuesta_bot' => $respuestaBot,
            'activo' => true,
            'metadata' => [
                'modelo' => 'virtual-assistant',
                'session_id' => $sessionId
            ]
        ]);
    }

}
