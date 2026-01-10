<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\productoController;

// Ruta de prueba (Ping)
Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

Route::prefix('v1')->group(function () {
    Route::prefix('negocio')->group(function () {
        // CRUD de negocios
        Route::get('/', 'App\Http\Controllers\NegocioController@index');
        Route::get('/{id}', 'App\Http\Controllers\NegocioController@show');
        Route::post('/', 'App\Http\Controllers\NegocioController@store');
        Route::put('/{id}', 'App\Http\Controllers\NegocioController@update');
        Route::delete('/{id}', 'App\Http\Controllers\NegocioController@destroy');

        Route::get('/{id}/products', [productoController::class, 'index']); // obetener todos los productos de un negocio
        Route::get('/{id}/products/{producto_id}', [productoController::class, 'show']);   // obtener un producto específico de un negocio
    });
     
});