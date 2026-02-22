<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Categoria\CrearCategoriaRequest;
use App\Http\Requests\Categoria\ActualizarCategoriaRequest;
use App\Http\Resources\CategoriaResource;
use App\Services\CategoriaService;
use App\Models\Categoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class CategoriasController extends Controller
{
    public function __construct(private CategoriaService $service) {}

public function index(Request $request): JsonResponse
{
    $query = Categoria::query();

    if ($request->filled('negocio_id')) {
        $query->where('id_negocio', $request->negocio_id);
    }

    $categorias = $query->orderBy('nombre')->get();

    return response()->json(CategoriaResource::collection($categorias));
}

    public function show(int $id): JsonResponse
    {
        $categoria = Categoria::findOrFail($id);
        return response()->json(new CategoriaResource($categoria));
    }

    public function store(CrearCategoriaRequest $request): JsonResponse
    {
        $categoria = $this->service->crear($request->validated());
        return response()->json(new CategoriaResource($categoria), 201);
    }

    public function update(int $id, ActualizarCategoriaRequest $request): JsonResponse
    {
        $categoria = $this->service->actualizar($id, $request->validated());
        return response()->json(new CategoriaResource($categoria));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->eliminar($id);
        return response()->json(['message' => 'Categoría eliminada'], 204);
    }
}
