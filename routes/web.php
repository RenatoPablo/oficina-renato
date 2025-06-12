<?php

use App\Http\Middleware\CheckIsNotLogged;
use App\Http\Middleware\CheckIsLogged;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\HomeController;

Route::get('/', function () {
    return view('welcome');
});

//auth routes - user not logged
Route::middleware([CheckIsNotLogged::class])->group(function () {
    Route::get('/login', [AuthController::class, 'login']);
    Route::post('/loginSubmit', [AuthController::class, 'loginSubmit']);
    
});

//auth routes- user is logged
Route::middleware([CheckIsLogged::class])->group(function () {
    Route::get('/', [HomeController::class, 'index']);

    //routes for clientes
    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('/clientes/{id}/edit', [ClienteController::class, 'edit'])->name('clientes.edit');
    Route::get('/clientes/create', [ClienteController::class, 'create'])->name('clientes.create');
    Route::post('/cliente/createSubmit', [ClienteController::class, 'createSubmit'])->name('clientes.create.submit');
    Route::put('/clientes/{id}', [ClienteController::class, 'editSubmit'])->name('clientes.update');

    Route::get('/logout', [AuthController::class, 'logout']);
});