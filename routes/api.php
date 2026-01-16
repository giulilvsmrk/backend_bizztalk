<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Negocio\NegocioController;


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
