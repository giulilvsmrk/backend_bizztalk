<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Producto;

class productoController extends Controller
{
    public function index()
    {
        // Lógica para obtener y retornar la lista de productos
        $productos = Producto::all();
        if ($productos->isEmpty()) {
            $data = [
                'message' => 'No hay productos disponibles',
                'status' => 200,
            ];
            return response()->json($data, 404);
        }else{
            return response()->json(['products' => $productos]);
        }
    }

    public function show($id)
    {
        
        // Lógica para obtener y retornar un producto específico por su ID
        return response()->json(['message' => "Detalles del producto con ID: $id"]);
    }   
}
