<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use App\Models\Producto;

class productoController extends Controller
{
    public function index($id) // id del negocio
    {
        $productos = Producto::where('id_negocio', $id)->get(); //
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

    public function show($producto_id)
    {
        $producto = Producto::where("id_producto",$producto_id); // Buscar el producto por su ID
        if (!$producto) {
            $data = [
                'message' => 'Producto no encontrado',
                'status' => 404,
            ];
            return response()->json($data, 404);
        }

        $data = [
            'message' => 'Producto encontrado',
            'status' => 200,
            'product' => $producto,
        ];
        // Lógica para obtener y retornar un producto específico por su ID
        return response()->json($data, 200);
    }

    public function showCategoria(Request $request)
    {
        $categoria = $request->get('name'); // obtener el nombre de la categoría desde los parámetros de la solicitud
        $id_Categoria = Categoria::where('nombre', $categoria)->value('id'); // obtener el ID de la categoría por su nombre
        $productos = Producto::where('id_categoria', $id_Categoria)->get(); // obtener productos por categoría

        if ($productos->isEmpty()) {
            $data = [
                'message' => 'No hay productos disponibles en esta categoría',
                'status' => 200,
            ];
            return response()->json($data, 404);
        } else {
            return response()->json(['products' => $productos]);
        }
    }
}
