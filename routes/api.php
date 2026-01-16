<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\productoController;

Route::prefix('v2')->group(function () {

    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
    });
});

Route::prefix('v1')->group(function () {
    Route::apiResource('usuarios', UsuarioController::class);
    Route::prefix('negocios/{negocioId}')->name('negocios.')->group(function () { //id de negocio para productos y categorias, id sucursal para promociones
        // CRUD de negocios
        Route::get('/', function (Request $request, $negocioId) {
            return response()->json(['message' => "Negocio ID: $negocioId"]);
        });
        /* Route::get('/{negocioId}', 'App\Http\Controllers\NegocioController@show');
        Route::post('/', 'App\Http\Controllers\NegocioController@store');
        Route::put('/{negocioId}', 'App\Http\Controllers\NegocioController@update');
        Route::delete('/{negocioId}', 'App\Http\Controllers\NegocioController@destroy'); */

        // Rutas de productos bajo un negocio específico
        Route::prefix('/products')->group(function () {
            Route::get('/', [productoController::class, 'index']); // obetener todos los productos de un negocio
            Route::get('/categoria', [productoController::class, 'showCategoria']);   // obtener productos por categoría, mediante query params
            Route::get('/{producto_id}', [productoController::class, 'show']);   // obtener un producto específico de un negocio
        
        });
        
        Route::get('/promociones', [App\Http\Controllers\Api\promocionController::class, 'index']); // lista promociones
    });
});