<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\negocioController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Api\productoController;
use App\Http\Controllers\Api\promocionController;
use App\Http\Controllers\Api\ChatbotNegocioController;
use App\Http\Controllers\Api\ChatbotResolverController;
use App\Http\Controllers\Api\ProductosController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\Api\CategoriasController;
use App\Http\Controllers\Api\LocalController;
Route::middleware('auth:sanctum')
    ->get('/mis-locales', [LocalController::class, 'misLocales']);
    
Route::prefix('categorias')->group(function () {
    Route::get('/', [CategoriasController::class, 'index']);          // Listar todas
    Route::get('{id}', [CategoriasController::class, 'show']);       // Ver una
    Route::post('/', [CategoriasController::class, 'store']);        // Crear
    Route::put('{id}', [CategoriasController::class, 'update']);     // Actualizar
    Route::delete('{id}', [CategoriasController::class, 'destroy']); // Eliminar
    Route::get('negocios/{negocio}/categorias', [CategoriasController::class, 'indexByNegocio']);
});
Route::prefix('productos')->group(function () {
    Route::post('/', [ProductosController::class, 'store']);  // Crear producto
    Route::get('/', [ProductosController::class, 'index']);   // Listar todos los productos
    Route::get('/{id}', [ProductosController::class, 'show']);  // Mostrar un producto específico
    Route::put('/{id}', [ProductosController::class, 'update']);     // Actualizar producto
    Route::delete('/{id}', [ProductosController::class, 'destroy']);     // Eliminar producto

});
Route::prefix('carrito')->group(function () {
    Route::get('/', [CarritoController::class, 'obtener']); // ?id_usuario=...
    Route::post('/', [CarritoController::class, 'agregar']); // body incluye id_usuario
    Route::put('/{idItem}', [CarritoController::class, 'actualizar']); // body cantidad y observacion
    Route::delete('/{idItem}', [CarritoController::class, 'eliminar']);
    Route::delete('/', [CarritoController::class, 'vaciar']); // ?id_usuario=...
});

Route::prefix('v1')->group(function () {

    Route::get('/ping', fn () => response()->json([
        'message' => 'pong',
        'service' => 'BizTalk Backend',
        'environment' => app()->environment(),
        'timestamp' => now()->toIso8601String()
    ]));

    // chatbot - Listar todos los negocios para el chatsito
    Route::prefix('chatbot')->name('chatbot.')->group(function () {
        Route::get('/negocios', [ChatbotNegocioController::class, 'listarTodos'])
            ->middleware('throttle:60,1')
            ->name('negocios.listado');
        Route::post('/resolver', [ChatbotResolverController::class, 'resolver'])
            ->middleware('throttle:60,1')
            ->name('resolver');
    });

    Route::controller(AuthController::class)->prefix('auth')->group(function () {
        Route::post('/registro', 'registro');
        Route::post('/login', 'login');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', fn (Request $request) => $request->user());

        /*Route::prefix('negocios')->controller(NegocioController::class)->group(function () {
            Route::post('/', 'store')->name('api.v1.negocios.store');
            Route::get('/propios', 'index');
            Route::get('/{id}', 'show');
            // Futuro: Route::put('/{id}', 'update');
            // Futuro: Route::delete('/{id}', 'destroy');
        });*/
    }); 
});

Route::prefix('v2')->group(function () {
    Route::post('/auth/login', [ApiAuthController::class, 'login'])
        ->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [ApiAuthController::class, 'logout']);
        Route::get('/auth/me', [ApiAuthController::class, 'me']);
    });
});

Route::prefix('v1')->group(function () {
    Route::apiResource('usuarios', UsuarioController::class);
    Route::get('/negocios', [negocioController::class, 'index']);
    Route::prefix('negocios/{negocioId}')->name('negocios.')->group(function () {

        Route::get('/', [negocioController::class, 'show']); // detalle de un negocio
        Route::get('/categorias', [negocioController::class, 'showCategorias']); // obtener categorías de un negocio

        Route::prefix('/products')->group(function () { // productos de un negocio
            Route::get('/', [productoController::class, 'index']); // listar todos los productos de un negocio
            Route::get('/categoria', [productoController::class, 'showCategoria']); // obtener productos por categoría
            Route::get('/{producto_id}', [productoController::class, 'show']); // obtener un producto específico
        });
        Route::get('/promociones', [promocionController::class, 'index']);
    });
});