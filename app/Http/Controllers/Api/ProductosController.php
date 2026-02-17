<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Producto\StoreProductoRequest;
use App\Actions\Producto\CrearProductoAction;
use App\DTOs\Producto\CrearProductoDTO;
use App\Http\Resources\ProductoResource;
use App\Actions\Producto\ListarProductoAction;
use App\Actions\Producto\MostrarProductoAction;
use App\Actions\Producto\ActualizarProductoAction;
use App\DTOs\Producto\ActualizarProductoDTO;
use App\Actions\Producto\EliminarProductoAction;
class ProductosController extends Controller
{
    public function store(
        StoreProductoRequest $request,
        CrearProductoAction $action
    )
    {
        $dto = CrearProductoDTO::fromArray($request->validated());

        $producto = $action->execute($dto);

        return (new ProductoResource($producto))
            ->response()
            ->setStatusCode(201);
    }
    public function index(ListarProductoAction $action)
{
    $productos = $action->execute();

    return ProductoResource::collection($productos);
}
public function show(string $id, MostrarProductoAction $action)
{
    $producto = $action->execute($id);

    return new ProductoResource($producto);
}

public function update(string $id, StoreProductoRequest $request, ActualizarProductoAction $action)
{
    $dto = ActualizarProductoDTO::fromArray($request->validated());
    $producto = $action->execute($id, $dto);

    return new ProductoResource($producto);
}

public function destroy($id, EliminarProductoAction $action)
{
    $action->execute($id);

    return response()->json(['message' => 'Producto eliminado correctamente.'], 200);
}

}