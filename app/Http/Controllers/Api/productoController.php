<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Inventario;
use App\Models\Sucursal;
use App\Models\Negocio;
use Illuminate\Support\Facades\Validator;

class productoController extends Controller
{
    // listar todos los productos de un negocio
    public function index($negocioId) // id del negocio
    {
        // verificar que el negocio existe (opcional)
        $negocio = Negocio::find($negocioId);
        if (is_null($negocio)) {
            $data = [
                'message' => 'Negocio no encontrado',
                'status' => 404,
            ];
            return response()->json($data, 404);
        } else {
            $productos = Producto::where('id_negocio', $negocioId)->get(); //
            if ($productos->isEmpty()) {
                $data = [
                    'message' => 'No hay productos disponibles',
                    'status' => 200,
                ];
                return response()->json($data, 404);
            } else {
                return response()->json(['products' => $productos]);
            }
        }
    }

    // Obtener un producto específico de un negocio
    public function show($negocioId, $producto_id)
    {
        $producto = Producto::where('id', $producto_id)
            ->where('id_negocio', $negocioId)
            ->first();

        if (!$producto) {
            $data = [
                'message' => 'Producto no encontrado para este negocio',
                'status' => 404,
            ];
            return response()->json($data, 404);
        } else {
            return response()->json(['product' => $producto]);
        }
    }

   
    public function showCategoria($negocioId, Request $request)
    {
       
        $request->validate([
            'name' => 'sometimes|string',
            'category_id' => 'sometimes|integer',
        ]);

        if (!$request->filled('name') && !$request->filled('category_id')) { // ninguno de los dos parámetros está presente
            return response()->json([
                'message' => 'Se requiere el parámetro query "name" o "category_id"'
            ], 422);
        }

        // Buscar la categoría dentro del negocio
        $categoria = Categoria::where('id_negocio', $negocioId)
            ->when($request->filled('category_id'), fn($q) => $q->where('id', $request->query('category_id'))) // filtrar por ID si se proporciona
            ->when($request->filled('name'), fn($q) => $q->where('nombre', $request->query('name'))) // filtrar por nombre si se proporciona
            ->first();

        if (!$categoria) {
            return response()->json([
                'message' => 'Categoría no encontrada para este negocio'
            ], 404);
        }

        // Obtener productos mediante la relación Eloquent
        $productos = $categoria->productos()->get();

        // Como ya no hay id_sucursal, devolvemos stock vacío
        $stock = collect();

        return response()->json([
            'category' => [
                'id' => $categoria->id,
                'name' => $categoria->nombre,
            ],
            'products' => $productos,
            'stock' => $stock,
            'count' => $productos->count(),
        ], 200);
    }

    public function store(Request $request, $negocioId)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required',
            'descripcion' => 'required',
            'precio_base' => 'required|numeric',
            'id_categoria' => 'required|exists:categoria,id',
            'imagen_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            $data = [
                'message' => 'error en la validacion de datos',
                'error' => $validator->errors(),
                'status' => 400
            ];
            return response()->json($data, 400);
        }

        $producto = Producto::create([
            'id_negocio' => $negocioId,
            'id_categoria' => $request->input('id_categoria'),
            'nombre' => $request->input('nombre'),
            'descripcion' => $request->input('descripcion'),
            'precio_base' => $request->input('precio_base'),
            'imagen_url' => $request->input('imagen_url'),
        ]);

        if (!$producto) {
            $data = [
                'message' => 'error al crear producto',
                'status' => 500
            ];
            return response()->json($data, 500);
        } else {
            $data = [
                'producto' => $producto,
                'status' => 201
            ];
            return response()->json($data, 201);
        }
    }
}
