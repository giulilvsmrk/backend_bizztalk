<?php

declare(strict_types=1);

namespace App\Modules\MessagingOrders\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Modules\MessagingOrders\Http\Requests\SendChatMessageRequest;
use App\Modules\MessagingOrders\Http\Requests\ConfirmarPedidoRequest;
use App\Modules\MessagingOrders\Services\ChatService;

/**
 * ChatController
 * Maneja peticiones del chat IA
 */
class ChatController extends Controller
{
    private ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * POST /api/ai/chat/send
     * Enviar mensaje y obtener respuesta de IA
     */
    public function send(SendChatMessageRequest $request): JsonResponse
    {
        // Aumentar límite de ejecución para Virtual Assistant
        set_time_limit(300);

        $datos = $request->validated();

        $resultado = $this->chatService->procesarMensaje(
            idNegocio: $datos['business_id'],
            mensajeUsuario: $datos['message'],
            limitHistorial: $datos['history_limit'],
            sessionId: $datos['session_id'] ?? null
        );

        if ($resultado['exito']) {
            return response()->json([
                'success' => true,
                'data' => $resultado['datos']
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $resultado['error'],
            'code' => $resultado['codigo'] ?? 'ERROR'
        ], 400);
    }

    /**
     * POST /api/ai/chat/confirmar-pedido
     * Confirmar y guardar pedido desde el chat
     */
    public function confirmarPedido(ConfirmarPedidoRequest $request): JsonResponse
    {
        $datos = $request->validated();

        $resultado = $this->chatService->confirmarPedido(
            sessionId: $datos['session_id'],
            idNegocio: $datos['business_id'],
            carrito: $datos['carrito'],
            total: $datos['total']
        );

        if ($resultado['exito']) {
            return response()->json([
                'success' => true,
                'data' => [
                    'pedido_id' => $resultado['pedido_id'],
                    'total' => $resultado['total']
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $resultado['error']
        ], 400);
    }

