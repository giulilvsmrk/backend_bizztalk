<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Chatbot\ChatbotNegocioResource;
use App\Models\Negocio;
use Illuminate\Http\JsonResponse;
 
class ChatbotNegocioController extends Controller
{
     
    @return JsonResponse
     
    public function listarTodos(): JsonResponse
    {
        // 1. Obtener TODOS los negocios activos de la BD
        $negocios = Negocio::where('activo', true)->get();

        // 2. Verificar que existan negocios
        if ($negocios->isEmpty()) {
            return response()->json([
                'message' => 'No hay negocios disponibles en este momento',
                'data' => [],
                'total' => 0
            ], 200);
        }

        // 3. Transformar con Resource y retornar
        return response()->json([
            'message' => 'Negocios obtenidos exitosamente',
            'data' => ChatbotNegocioResource::collection($negocios),
            'total' => $negocios->count()
        ], 200);
    }
}

