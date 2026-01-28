<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\isNull;

class negocioController extends Controller
{
    public function index()
    {
        $negocios = Negocio::all();
        if (is_null($negocios)) {
            $data = [
                'message' => 'Negocio no encontrado',
                'status' => 404,
            ];
            return response()->json($data, 404);
        } else {
            $data = [
                'message' => 'Lista de negocios',
                'status' => 200,
                'negocios' => $negocios,
            ];
            return response()->json($data, 200);
        }
    }

    public function show($negocioId)
    {
        $negocio = Negocio::find($negocioId);
        if (is_null($negocio)) {
            $data = [
                'message' => 'Negocio no encontrado',
                'status' => 404,
            ];
            return response()->json($data, 404);
        } else {
            $data = [
                'message' => 'Detalle del negocio',
                'status' => 200,
                'negocio' => $negocio,
            ];
            return response()->json($data, 200);
        }
    }

    public function store(Request $request)
    {
        // Validar los datos de entrada
        $validatedData = $request->validate([
            'id_propietario' => 'required|uuid|exists:usuarios,id',
            'nombre' => 'required|string|max:255',
            'nit' => 'required|string|max:20|unique:negocios,nit',
            'descripcion' => 'nullable|string',
            'logotipo_url' => 'nullable|url',
        ]);

        // Crear el nuevo negocio
        $negocio = Negocio::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'id_propietario' => $validatedData['id_propietario'],
            'nombre' => $validatedData['nombre'],
            'nit' => $validatedData['nit'],
            'descripcion' => $validatedData['descripcion'] ?? null,
            'logotipo_url' => $validatedData['logotipo_url'] ?? null,
            'activo' => true,
            'fecha_creacion' => now(),
        ]);

        // Retornar la respuesta
        return response()->json([
            'message' => 'Negocio creado exitosamente',
            'status' => 201,
            'negocio' => $negocio,
        ], 201);
    }

    public function update(Request $request, $negocioId)
    {
        // Lógica para actualizar un negocio (futuro)
    }

    public function showCategorias($negocioId)
    {
        $categorias = Categoria::where('id_negocio', $negocioId)->get();
        if ($categorias->isEmpty()) {
            $data = [
                'message' => 'No hay categorías disponibles',
                'status' => 200,
            ];
            return response()->json($data, 404);
        } else {
            $data = [
                'message' => 'Lista de categorías',
                'status' => 200,
                'categorias' => $categorias,
            ];
            return response()->json($data, 200);
        }
    }


}
