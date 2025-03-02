<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ClientesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PolizaController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\ServicioController;

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

Route::prefix('facturas')->group(function () {
    Route::get('/', [FacturaController::class, 'index']);
    Route::get('/clientes', [FacturaController::class, 'getClientes']); // Nueva ruta
    Route::post('/', [FacturaController::class, 'store']);
    //Route::get('/{id}', [FacturaController::class, 'show']);
    Route::post('/{id}', [FacturaController::class, 'update']);
    Route::delete('/{id}', [FacturaController::class, 'destroy']);
});

Route::prefix('servicios')->group(function () {
    Route::get('/', [ServicioController::class, 'index']);
    Route::get('/tecnicos', [ServicioController::class, 'getTecnicos']);
    Route::get('/clientes', [ServicioController::class, 'getClientes']);
    Route::post('/', [ServicioController::class, 'store']);
    Route::get('/{id}', [ServicioController::class, 'show']);
    Route::post('/{id}', [ServicioController::class, 'update']);
    Route::delete('/{id}', [ServicioController::class, 'destroy']);
    Route::get('/cliente/{clienteId}/polizas', [ServicioController::class, 'getPolizasByCliente']);
});
