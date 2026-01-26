<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatbotBusinessResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotResolverController extends Controller
{
    /**
     * Resolver un negocio por texto del usuario
     * 
     * @param Request $request
     * @param ChatbotBusinessResolver $resolver
     * @return JsonResponse
     */
    public function resolver(Request $request, ChatbotBusinessResolver $resolver): JsonResponse
    {
        $validated = $request->validate([
            'texto' => 'required|string|max:255'
        ]);

        $result = $resolver->resolve($validated['texto']);

        return response()
            ->json($result, 200)
            ->header('Content-Type', 'application/json; charset=utf-8')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
