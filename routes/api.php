<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsuarioController;

Route::prefix('v1')->group(function () {
    Route::apiResource('usuarios', UsuarioController::class);
});