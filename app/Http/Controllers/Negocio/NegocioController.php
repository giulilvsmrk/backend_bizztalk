<?php

declare(strict_types=1);

namespace App\Http\Controllers\Negocio;

use App\Actions\Negocio\ListarNegociosPropiosAction;
use App\Actions\Negocio\ObtenerNegocioPorIdAction;
use App\Actions\Negocio\RegistrarNegocioAction;
use App\DTOs\Negocio\RegistroNegocioDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Negocio\RegistroNegocioRequest;
use App\Http\Resources\Negocio\NegocioCardResource;
use App\Http\Resources\Negocio\NegocioDetalleResource;
use App\Http\Resources\Negocio\NegocioResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Orquestador principal para la gestión de Negocios.
 * Maneja el ciclo de vida: Listado (Read), Creación (Create) y Detalle (Read).
 */
class NegocioController extends Controller
{
    /**
     * Listar todos los negocios del usuario autenticado.
     * Ideal para la pantalla de selección de empresa ("Mis Negocios").
     *
     * @param Request $request
     * @param ListarNegociosPropiosAction $action
     * @return AnonymousResourceCollection
     */
    public function index(Request $request, ListarNegociosPropiosAction $action): AnonymousResourceCollection
    {
        // 1. Delegar: La acción trae los negocios y precarga eficientemente la imagen de portada.
        $negocios = $action->ejecutar($request->user());

        // 2. Responder: Usamos el recurso "Card" que es ligero y tiene la estructura resumida.
        return NegocioCardResource::collection($negocios);
    }

    /**
     * Registrar un nuevo negocio junto con su sucursal matriz.
     *
     * @param RegistroNegocioRequest $request Validación estricta.
     * @param RegistrarNegocioAction $action Lógica transaccional.
     * @return NegocioResource
     */
    public function store(RegistroNegocioRequest $request, RegistrarNegocioAction $action): NegocioResource
    {
        // 1. Transformar: Convertimos el Request validado a DTO.
        $dto = RegistroNegocioDTO::desdeRequest($request);

        // 2. Delegar: Ejecutamos la transacción de creación.
        $negocio = $action->ejecutar($dto, $request->user());

        // 3. Responder: Usamos el recurso original completo para confirmar
        // que se crearon tanto el negocio como la sucursal.
        return new NegocioResource($negocio);
    }

    /**
     * Ver el perfil administrativo de un negocio específico.
     *
     * @param string $id UUID del negocio.
     * @param ObtenerNegocioPorIdAction $action Acción de búsqueda optimizada.
     * @return NegocioDetalleResource
     */
    public function show(string $id, ObtenerNegocioPorIdAction $action): NegocioDetalleResource
    {
        // 1. Delegar: Busca el negocio por ID (lanzará 404 si no existe).
        $negocio = $action->ejecutar($id);

        // 2. Seguridad: (Opcional pero recomendado) Verificar que sea el dueño.
        // En un futuro Middleware 'can:view,negocio' se encargaría de esto.
        if ($negocio->id_propietario !== request()->user()->id) {
             abort(403, 'No tienes permiso para ver este negocio.');
        }

        // 3. Responder: Usamos el recurso "Detalle" que incluye descripción completa
        // y la portada, pero EXCLUYE la lista de sucursales (para no sobrecargar).
        return new NegocioDetalleResource($negocio);
    }
}
