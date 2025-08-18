<?php

use App\Http\Middleware\CheckIsNotLogged;
use App\Http\Middleware\CheckIsLogged;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EstoqueController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrdemServicoController;
use App\Http\Controllers\ServicoController;
use App\Http\Controllers\VeiculoController;
use App\Models\Estoque;
use App\Models\Veiculo;
use League\CommonMark\Extension\CommonMark\Parser\Inline\EscapableParser;

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
    Route::get('/', [HomeController::class, 'index'])->name('dashboard');

    //routes for clientes
    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('/clientes/{id}/edit', [ClienteController::class, 'edit'])->name('clientes.edit');
    Route::get('/clientes/create', [ClienteController::class, 'create'])->name('clientes.create');
    Route::post('/cliente/createSubmit', [ClienteController::class, 'createSubmit'])->name('clientes.create.submit');
    Route::put('/clientes/{id}', [ClienteController::class, 'editSubmit'])->name('clientes.update');
    Route::delete('/clientes/{id}', [ClienteController::class, 'destroy'])->name('clientes.destroy');

    //routes for estoques
    Route::get('/estoque', [EstoqueController::class, 'index'])->name(('estoque.index'));
    Route::get('/estoque/crete', [EstoqueController::class, 'create'])->name('estoque.create');
    Route::post('/estoque/createSubmit', [EstoqueController::class, 'createSubmit'])->name('estoque.create.submit');
    Route::get('/estoque/{id}/edit', [EstoqueController::class, 'edit'])->name('estoque.edit');
    Route::put('/estoque/{id}', [EstoqueController::class, 'editSubmit'])->name('estoque.update');
    Route::delete('cliente/{id}', [EstoqueController::class, 'destroy'])->name('estoque.destroy');

    //routes for serviços
    Route::get('/servicos', [ServicoController::class, 'index'])->name(('servico.index'));
    Route::get('/servico/create', [ServicoController::class, 'create'])->name(('servico.create'));
    Route::post('/servico/createSubmit', [ServicoController::class, 'createSubmit'])->name(('servico.create.submit'));
    Route::get('/servico/{id}/edit', [ServicoController::class, 'edit'])->name(('servico.edit'));
    Route::put('/servico/{id}', [ServicoController::class, 'editSubmit'])->name(('servico.update'));
    Route::delete('/servico/{id}', [ServicoController::class, 'destroy'])->name(('servico.destroy'));

    //routes for veiculos
    Route::get('/veiculos', [VeiculoController::class, 'index'])->name(('veiculo.index'));
    Route::get('/veiculo/create', [VeiculoController::class, 'create'])->name(('veiculo.create'));
    Route::post('/veiculo/createSubmit', [VeiculoController::class, 'createSubmit'])->name(('veiculo.create.submit'));
    Route::get('/veiculo/{id}/edit', [VeiculoController::class, 'edit'])->name(('veiculo.edit'));
    Route::put('/veiculo/{id}', [VeiculoController::class, 'editSubmit'])->name(('veiculo.update'));
    //route for desassociar cliente
    Route::put('/veiculo/{id}/desassociar-cliente', [VeiculoController::class, 'desassociarCliente'])->name(('veiculo.desassociar.cliente'));
    Route::delete('/veiculo/{id}', [VeiculoController::class, 'destroy'])->name(('veiculo.destroy'));
    Route::get('/veiculo/{id}/historico', [VeiculoController::class, 'historicoProprietario'])->name(('veiculo.historico.proprietario'));



    //routes for ordem servico
    Route::get('/ordem', [OrdemServicoController::class, 'index'])->name(('ordem.index'));
    Route::get('/ordem/create', [OrdemServicoController::class, 'create'])->name(('ordem.create'));
    Route::post('/ordem/store', [OrdemServicoController::class, 'store'])->name(('ordem.store'));
    Route::get('/ordem/{id}/edit', [OrdemServicoController::class, 'edit'])->name(('ordem.edit'));
    
    //route for sync servico_ordem with ordem_servico
    //conferir se esta usando depois
    Route::post('/ordem/{id}/servico/create', [OrdemServicoController::class, 'servicosSync'])->name(('ordem.servico.sync'));

    // salvar tudo de uma vez (servicos + pecas + frete)
    Route::post('/ordem/{id}/sync-all', [OrdemServicoController::class, 'syncAll'])->name('ordem.syncAll');



    Route::get('/logout', [AuthController::class, 'logout'])->name(('logout'));
});