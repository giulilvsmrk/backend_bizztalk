<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Negocio\NegocioController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Api\productoController;
use App\Http\Controllers\Api\promocionController;

Route::prefix('v1')->group(function () {

    Route::get('/ping', fn () => response()->json([
        'message' => 'pong',
        'service' => 'BizTalk Backend',
        'environment' => app()->environment(),
        'timestamp' => now()->toIso8601String()
    ]));

    Route::controller(AuthController::class)->prefix('auth')->group(function () {
        Route::post('/registro', 'registro');
        Route::post('/login', 'login');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', fn (Request $request) => $request->user());

        Route::prefix('negocios')->controller(NegocioController::class)->group(function () {
            Route::post('/', 'store')->name('api.v1.negocios.store');
            Route::get('/propios', 'index');
            Route::get('/{id}', 'show');
            // Futuro: Route::put('/{id}', 'update');
            // Futuro: Route::delete('/{id}', 'destroy');
        });
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
    Route::prefix('negocios/{negocioId}')->name('negocios.')->group(function () {

        Route::get('/', function (Request $request, $negocioId) {
            return response()->json(['message' => "Negocio ID: $negocioId"]);
        });
        Route::prefix('/products')->group(function () {
            Route::get('/', [productoController::class, 'index']);
            Route::get('/categoria', [productoController::class, 'showCategoria']);
            Route::get('/{producto_id}', [productoController::class, 'show']);
        });

        Route::get('/promociones', [promocionController::class, 'index']);
    });
});
