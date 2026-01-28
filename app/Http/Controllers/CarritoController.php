<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Carrito\AgregarItemCarritoAction;
use App\Actions\Carrito\ActualizarItemCarritoAction;
use App\Actions\Carrito\EliminarItemCarritoAction;
use App\Actions\Carrito\VaciarCarritoAction;
use App\Http\Requests\AgregarItemCarritoRequest;
use App\Http\Requests\ActualizarItemCarritoRequest;
use App\Http\Resources\CarritoResource;
use App\Services\CarritoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CarritoController extends Controller
{
    public function obtener(Request $request, CarritoService $service): JsonResponse
    {
        $idUsuario = $request->query('id_usuario');
        if (!$idUsuario) {
            return response()->json(['error' => 'Debe enviar id_usuario'], 400);
        }

        $carrito = $service->obtenerCarrito($idUsuario);
        return response()->json(new CarritoResource($carrito));
    }

    public function agregar(AgregarItemCarritoRequest $request, AgregarItemCarritoAction $action): JsonResponse
    {
        $item = $action->execute(
            $request->id_usuario,
            $request->id_producto,
            $request->cantidad,
            $request->observacion
        );

        return response()->json(new CarritoResource($item->carrito));
    }

    public function actualizar(int $idItem, ActualizarItemCarritoRequest $request, ActualizarItemCarritoAction $action): JsonResponse
    {
        $item = $action->execute(
            $idItem,
            $request->cantidad,
            $request->observacion
        );

        return response()->json(new CarritoResource($item->carrito));
    }

    public function eliminar(int $idItem, EliminarItemCarritoAction $action): JsonResponse
    {
        $action->execute($idItem);
        return response()->json(['message' => 'Item eliminado correctamente']);
    }

    public function vaciar(Request $request, VaciarCarritoAction $action): JsonResponse
    {
        $idUsuario = $request->query('id_usuario');
        if (!$idUsuario) {
            return response()->json(['error' => 'Debe enviar id_usuario'], 400);
        }

        $action->execute($idUsuario);
        return response()->json(['message' => 'Carrito vaciado correctamente']);
    }
}
