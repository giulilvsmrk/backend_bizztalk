<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Models\Usuario;
use App\Services\UsuarioService;
use Illuminate\Http\JsonResponse;

class UsuarioController extends Controller
{
    protected UsuarioService $service;

    public function __construct(UsuarioService $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        $usuarios = $this->service->getAll();
        return response()->json($usuarios);
    }

    public function store(StoreUsuarioRequest $request): JsonResponse
    {
        $usuario = $this->service->create($request->validated());
        return response()->json($usuario, 201);
    }

    public function show(string $id): JsonResponse
    {
        $usuario = $this->service->find($id);
        return response()->json($usuario);
    }

    public function update(UpdateUsuarioRequest $request, Usuario $usuario): JsonResponse
    {
        $usuario = $this->service->update($usuario, $request->validated());
        return response()->json($usuario);
    }

    public function destroy(Usuario $usuario): JsonResponse
    {
        $this->service->delete($usuario);
        return response()->json(['message' => 'Usuario eliminado correctamente']);
    }
}
