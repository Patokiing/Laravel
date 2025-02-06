<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ClientesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('login', [LoginController::class,'login']);

Route::post('register', [RegisterController::class,'register']);

Route::get('clientes', [ClientesController::class,'index']);
Route::get('cliente/{$id}', [ClientesController::class,'client']);
Route::post('cliente/guardar', [ClientesController::class,'store']);
Route::delete('cliente/eliminar/{$id}', [ClientesController::class,'destroy']);