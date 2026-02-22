<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use Illuminate\Http\Request;

class LocalController extends Controller
{
    public function misLocales(Request $request)
    {
        $usuario = $request->user();

        $negocios = $usuario->negociosPropios()
            ->where('activo', true)
            ->withCount([
                'productos as products_count' => fn($q) => $q->where('activo', true),
                'categorias as categories_count'
            ])
            ->with([
                'telefonosCentral' => fn($q) => $q->principal()
            ])
            ->get();

        $data = $negocios->map(function ($negocio) {

            $telefonoPrincipal = $negocio->telefonosCentral->first()?->numero;

            return [
                'id' => $negocio->id, // 👈 ESTE ES EL ID DEL LOCAL
                'image' => $negocio->logotipo_url,
                'name' => $negocio->nombre,
                'address' => $negocio->direccion_texto,
                'phone' => $telefonoPrincipal,
                'products' => $negocio->products_count,
                'categories' => $negocio->categories_count,
            ];
        });

        return response()->json($data);
    }
}