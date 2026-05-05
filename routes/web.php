<?php

use App\Http\Controllers\DeficienciaController;
use App\Http\Controllers\DiagnosticoController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EscolaController;
use App\Http\Controllers\DocumentosEscolaController;
use App\Http\Controllers\ProfissionalController;
use App\Http\Controllers\DocumentosProfissionalController;
use App\Http\Controllers\ListaEsperaController;
use App\Http\Controllers\OrigemEncaminhamentoController;
use App\Http\Controllers\AlunoController;
use App\Http\Controllers\DocumentoAlunoController;
use App\Http\Controllers\HorarioProfissionalController;
use App\Http\Controllers\AgendamentoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContaController;
use App\Http\Controllers\LogAtividadeController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\RegistroAtendimentoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\UserController;

Route::middleware('auth')->group(function () {

    //Controle de atividades da conta
    Route::get('/logs', [LogAtividadeController::class, 'index'])->name('logs.index');


    //Rotas para configuração da conta
    Route::get('/conta', [ContaController::class, 'index'])->name('conta.index');
    Route::put('/conta/perfil', [ContaController::class, 'updatePerfil'])->name('conta.perfil');
    Route::put('/conta/senha', [ContaController::class, 'updateSenha'])->name('conta.senha');

    Route::get('/index', [DashboardController::class, 'index'])->name('index');
    Route::get('/sobre', fn() => view('sobre'))->name('sobre');

    //Confirmar as permissões dos usuários logados
    Route::middleware('permissao:usuarios.gerenciar')->group(function () {
        Route::resource('usuarios', UserController::class);
        Route::resource('perfis', PerfilController::class)->only(['index', 'store', 'update', 'destroy'])->parameters(['perfis' => 'perfil']);
    });

    Route::middleware('permissao:escolas.gerenciar')->group(function () {
        Route::resource('escolas', EscolaController::class);
        Route::delete('/documentos/{id}', [DocumentosEscolaController::class, 'destroy'])->name('documentos.destroy');
    });

    Route::middleware('permissao:alunos.gerenciar')->group(function () {
        Route::resource('alunos', AlunoController::class)->except(['destroy']);
        Route::patch('alunos/{aluno}/toggle', [AlunoController::class, 'toggle'])->name('alunos.toggle');
        Route::delete('/alunos/documentos/{id}', [DocumentoAlunoController::class, 'destroy'])->name('alunos.documentos.destroy');
        Route::get('/alunos/{aluno}/ficha', [AlunoController::class, 'ficha'])->name('alunos.ficha');
    });

    Route::middleware('permissao:profissionais.gerenciar')->group(function () {
        Route::delete('/profissionais/documentos/{id}', [DocumentosProfissionalController::class, 'destroy'])->name('profissionais.documentos.destroy');
        Route::resource('profissionais', ProfissionalController::class)->parameters(['profissionais' => 'profissional'])->except(['destroy']);
        Route::patch('profissionais/{profissional}/toggle', [ProfissionalController::class, 'toggle'])->name('profissionais.toggle');
        Route::get('horarios', [HorarioProfissionalController::class, 'index'])->name('horarios.index');
        Route::get('horarios/{profissional}', [HorarioProfissionalController::class, 'show'])->name('horarios.show');
        Route::post('horarios/{profissional}', [HorarioProfissionalController::class, 'store'])->name('horarios.store');
        Route::get('horarios/{profissional}/{horario}/edit', [HorarioProfissionalController::class, 'edit'])->name('horarios.edit');
        Route::put('horarios/{profissional}/{horario}', [HorarioProfissionalController::class, 'update'])->name('horarios.update');
        Route::delete('horarios/{profissional}/{horario}', [HorarioProfissionalController::class, 'destroy'])->name('horarios.destroy');
        Route::patch('horarios/{profissional}/{horario}/toggle', [HorarioProfissionalController::class, 'toggle'])->name('horarios.toggle');
    });

    Route::middleware('permissao:agendamentos.visualizar,agendamentos.gerenciar')->group(function () {
        Route::get('/agendamento', [AgendamentoController::class, 'index'])->name('agendamentos');
        Route::get('/agendamento/horarios', [AgendamentoController::class, 'horarios'])->name('agendamentos.horarios');
        Route::get('/agendamento/profissionais/{alunoId}', [AgendamentoController::class, 'profissionaisPorAluno'])->name('agendamentos.profissionais');
        Route::get('/agendamento/relatorio/{alunoId}', [AgendamentoController::class, 'relatorioAluno'])->name('agendamentos.relatorio');
        Route::get('/agendamento/relatorio-profissional/{profissionalId}', [AgendamentoController::class, 'relatorioProfissional'])->name('agendamentos.relatorio.profissional');
        Route::get('/agendamento/imprimir', [AgendamentoController::class, 'imprimir'])->name('agendamentos.imprimir');
    });

    Route::middleware('permissao:agendamentos.gerenciar')->group(function () {
        Route::get('/agendamento/criar', [AgendamentoController::class, 'create'])->name('agendamentos.create');
        Route::post('/agendamento', [AgendamentoController::class, 'store'])->name('agendamentos.store');
        Route::get('/agendamento/{id}/editar', [AgendamentoController::class, 'edit'])->name('agendamentos.edit');
        Route::put('/agendamento/{id}', [AgendamentoController::class, 'update'])->name('agendamentos.update');
        Route::delete('/agendamento/{id}', [AgendamentoController::class, 'destroy'])->name('agendamentos.destroy');
    });

    Route::middleware('permissao:atendimentos.gerenciar')->group(function () {
        Route::get('/atendimentoLancar', [RegistroAtendimentoController::class, 'index'])->name('atendimento.lancar');
        Route::get('/atendimento/{alunoId}/novo', [RegistroAtendimentoController::class, 'create'])->name('atendimento.form');
        Route::post('/atendimento', [RegistroAtendimentoController::class, 'store'])->name('atendimento.store');
        Route::get('/atendimento/{id}/editar', [RegistroAtendimentoController::class, 'edit'])->name('atendimento.edit');
        Route::put('/atendimento/{id}', [RegistroAtendimentoController::class, 'update'])->name('atendimento.update');
        Route::delete('/atendimento/documento/{id}', [RegistroAtendimentoController::class, 'destroyDocumento'])->name('atendimento.documento.destroy');
    });

    Route::middleware('permissao:listas_espera.gerenciar')->group(function () {
        Route::get('listasEspera/filas', [ListaEsperaController::class, 'filas'])->name('listasEspera.filas');
        Route::resource('listasEspera', ListaEsperaController::class)->parameters(['listasEspera' => 'lista'])->only(['index', 'store', 'update']);
        Route::patch('listasEspera/{lista}/toggle', [ListaEsperaController::class, 'toggle'])->name('listasEspera.toggle');
    });

    Route::middleware('permissao:configuracoes.gerenciar')->group(function () {
        Route::resource('diagnosticos', DiagnosticoController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('deficiencias', DeficienciaController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('origensEncaminhamento', OrigemEncaminhamentoController::class)->only(['index', 'store', 'update', 'destroy']);
    });

    Route::middleware('permissao:relatorios.visualizar')->group(function(){
        Route::get('/relatorios/atendimentos', [RelatorioController::class, 'atendimentos'])->name('relatorios.atendimentos');
    });

});

Route::view('/login', 'auth.login')->name('login.form');

Route::post('/auth', [LoginController::class, 'auth'])->middleware('throttle:5,1')->name('login.auth');

Route::get('/', fn () => redirect()->route('login.form'));

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
