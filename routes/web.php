<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BibliotecaController;
use App\Http\Controllers\BuscarController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JuegoController;
use App\Http\Controllers\ListaController;
use App\Http\Controllers\PerfilController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/buscar', [BuscarController::class, 'index'])->name('buscar');
Route::get('/api/buscar', [BuscarController::class, 'api'])->middleware('throttle:90,1');
Route::get('/juegos/{game}', [JuegoController::class, 'show'])->name('juego');
Route::get('/u/{user}', [PerfilController::class, 'show'])->name('perfil');
Route::get('/listas/{lista}', [ListaController::class, 'show'])->name('listas.show');

Route::middleware('guest')->group(function () {
    Route::get('/entrar', [AuthController::class, 'login'])->name('login');
    Route::post('/entrar', [AuthController::class, 'entrar'])->middleware('throttle:10,1');
    Route::get('/registro', [AuthController::class, 'registro'])->name('registro');
    Route::post('/registro', [AuthController::class, 'registrar'])->middleware('throttle:10,1');
    Route::get('/demo', [AuthController::class, 'demo'])->name('demo');
});

Route::middleware('auth')->group(function () {
    Route::post('/salir', [AuthController::class, 'salir'])->name('salir');
    Route::get('/biblioteca', BibliotecaController::class)->name('biblioteca');
    Route::get('/perfil', [PerfilController::class, 'propio'])->name('perfil.propio');
    Route::post('/juegos/{game}/guardar', [JuegoController::class, 'guardar'])->name('juego.guardar');
    Route::delete('/juegos/{game}', [JuegoController::class, 'quitar'])->name('juego.quitar');
    Route::get('/listas', [ListaController::class, 'index'])->name('listas.index');
    Route::post('/listas', [ListaController::class, 'store'])->name('listas.store');
    Route::delete('/listas/{lista}', [ListaController::class, 'destroy'])->name('listas.destroy');
    Route::post('/juegos/{game}/lista', [ListaController::class, 'agregar'])->name('listas.agregar');
    Route::delete('/listas/{lista}/juegos/{game}', [ListaController::class, 'quitar'])->name('listas.quitar');
});
