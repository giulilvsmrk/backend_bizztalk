<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Promocion;

class promocionController extends Controller
{
    public function index($negocioId)
    {
        // Implementación para obtener promociones de un negocio específico
        $promocion = Promocion::where('id_sucursal', $negocioId)->first();
        if (is_null($promocion)) {
            $data = [
                'message' => 'Promocion no encontrada',
                'status' => 404,
            ];
            return response()->json($data, 404);
        } else {
            $promociones = Promocion::where('id_sucursal', $negocioId)->get(); //
            if ($promociones->isEmpty()) {
                $data = [
                    'message' => 'No hay promociones disponibles',
                    'status' => 200,
                ];
                return response()->json($data, 404);
            } else {
                return response()->json(['promociones' => $promociones]);
            }
        }

    }
}
