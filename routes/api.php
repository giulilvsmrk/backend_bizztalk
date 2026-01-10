<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\productoController;

// Ruta de prueba (Ping)
Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

Route::prefix('v1')->group(function () {
    Route::prefix('negocios/{negocioId}')->name('negocios.')->group(function () {
        // CRUD de negocios
       /*  Route::get('/', 'App\Http\Controllers\NegocioController@index');
        Route::get('/{negocioId}', 'App\Http\Controllers\NegocioController@show');
        Route::post('/', 'App\Http\Controllers\NegocioController@store');
        Route::put('/{negocioId}', 'App\Http\Controllers\NegocioController@update');
        Route::delete('/{negocioId}', 'App\Http\Controllers\NegocioController@destroy'); */

        // Rutas de productos bajo un negocio específico
        Route::prefix('/products')->group(function () {
            Route::get('/', [productoController::class, 'index']); // obetener todos los productos de un negocio
            Route::get('/categoria', [productoController::class, 'showCategoria']);   // obtener productos por categoría, mediante query params
            Route::get('/{producto_id}', [productoController::class, 'show']);   // obtener un producto específico de un negocio
        
        }); 
    });
});