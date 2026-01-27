<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use Illuminate\Http\Request;

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
        return response()->json(['message' => "Detalles del negocio ID: $negocioId"]);
    }
}
