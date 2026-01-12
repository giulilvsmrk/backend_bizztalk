<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Modules\MessagingOrders\Http\Controllers\ChatController;

// Ruta de prueba (Ping)
Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

// Rutas del módulo MessagingOrders - Chat IA
Route::prefix('ai/chat')->group(function () {
    Route::post('/send', [ChatController::class, 'send']);
    Route::post('/confirmar-pedido', [ChatController::class, 'confirmarPedido']);
    Route::get('/status', [ChatController::class, 'status']);
});
