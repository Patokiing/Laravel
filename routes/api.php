<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ClientesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PolizaController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('login', [LoginController::class,'login']);

Route::post('register', [RegisterController::class,'register']);
Route::post('/users/{id}', [RegisterController::class, 'update']);
Route::get('/users/{id}', [RegisterController::class, 'show']);
Route::get('/users', [RegisterController::class, 'index']);
Route::delete('/users/{id}', [RegisterController::class, 'destroy']);

Route::get('clientes', [ClientesController::class,'index']);
Route::get('cliente/{$id}', [ClientesController::class,'client']);
Route::post('cliente/guardar', [ClientesController::class,'store']);
Route::delete('cliente/eliminar/{$id}', [ClientesController::class,'destroy']);


Route::prefix('polizas')->group(function () {
    Route::get('/', [PolizaController::class, 'index']);
    Route::post('/', [PolizaController::class, 'store']);
    Route::get('/{id}', [PolizaController::class, 'show']);
    Route::post('/{id}', [PolizaController::class, 'update']);
    Route::delete('/{id}', [PolizaController::class, 'destroy']);
});
