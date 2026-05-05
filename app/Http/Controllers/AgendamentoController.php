<?php

namespace App\Http\Controllers;

use App\Models\Agendamento;
use App\Models\Profissional;
use Illuminate\Http\Request;
use App\Models\Aluno;
use App\Models\HorarioProfissional;
use App\Models\ListaEspera;
use App\Traits\RegistraLog;

class AgendamentoController extends Controller
{
    use RegistraLog;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $dataSelecionada  = $request->query('data', today()->toDateString());
        $alunoId          = $request->query('aluno_id');
        $modo             = $request->query('modo', 'dia');
        $dataCarbon       = \Carbon\Carbon::parse($dataSelecionada);
        $diaSemana        = $dataCarbon->dayOfWeek;

        $profissionalVinculado = auth()->user()->profissional;
        $profissionalId = $profissionalVinculado
            ? $profissionalVinculado->id
            : $request->query('profissional_id');

        $profissionais = Profissional::where('ativo', true)
            ->when($alunoId, fn($q) => $q->whereHas('horarios.agendamentos', fn($q2) =>
                $q2->where('aluno_id', $alunoId)->where('status', 'agendado')
            ))
            ->orderBy('nome')->get();

        $alunos = Aluno::whereHas('agendamentos', function ($q) use ($profissionalId) {
                $q->where('status', 'agendado');
                if ($profissionalId) {
                    $q->whereHas('horarioProfissional', fn($q2) =>
                        $q2->where('profissional_id', $profissionalId)
                    );
                }
            })
            ->orderBy('nome')->get(['id', 'nome']);

        $query = Agendamento::with(['aluno', 'listaEspera', 'horarioProfissional.profissional'])
            ->where('status', 'agendado')
            ->when($alunoId, fn($q) => $q->where('aluno_id', $alunoId));

        if ($modo === 'dia') {
            $query->whereHas('horarioProfissional', function ($q) use ($diaSemana, $profissionalId) {
                $q->where('dia_semana', $diaSemana)->where('ativo', true);
                if ($profissionalId) $q->where('profissional_id', $profissionalId);
            });
        } else {
            $query->whereHas('horarioProfissional', function ($q) use ($profissionalId) {
                $q->where('ativo', true);
                if ($profissionalId) $q->where('profissional_id', $profissionalId);
            });
        }

        $agendamentos = $query->get()->sortBy([
            ['horarioProfissional.dia_semana', 'asc'],
            ['horarioProfissional.hora_inicio', 'asc'],
        ]);

        $ocorrenciasMes = [];
        if ($modo === 'mes') {
            $inicioMes = $dataCarbon->copy()->startOfMonth();
            $fimMes    = $dataCarbon->copy()->endOfMonth();
            for ($dia = 0; $dia <= 6; $dia++) {
                $count   = 0;
                $current = $inicioMes->copy();
                while ($current->lte($fimMes)) {
                    if ($current->dayOfWeek === $dia) $count++;
                    $current->addDay();
                }
                $ocorrenciasMes[$dia] = $count;
            }
        }

        $profissionalFixo = $profissionalVinculado !== null;

        return view('atendimento.index', compact(
            'agendamentos', 'profissionais', 'alunos',
            'dataSelecionada', 'alunoId', 'profissionalId', 'profissionalFixo',
            'modo', 'ocorrenciasMes'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $search = $request->query('q');
        $query = Aluno::with('escola')
            ->where('ativo', true)
            ->whereHas('listasEspera', fn($q) => $q->where('lista_espera_aluno.status', 'aguardando'))
            ->orderBy('nome');

        if ($search) {
            $query->where('nome', 'like', "%{$search}%");
        }

        $alunos = $query->get();
        $profissionais = Profissional::where('ativo', true)->orderBy('nome')->get();
        $listasEspera = ListaEspera::where('ativo', true)->orderBy('nome')->get();

        return view('atendimento.postAtendimento', compact('alunos', 'profissionais', 'listasEspera'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'aluno_id'                 => 'required|exists:alunos,id',
            'lista_espera_id'          => 'required|exists:listas_espera,id',
            'horarios_profissional_id' => 'required|exists:horarios_profissional,id',
            'observacoes'              => 'nullable|string|max:1000',
        ]);

        $horario = HorarioProfissional::findOrFail($validated['horarios_profissional_id']);
        $hoje    = today();

        $validated['data']   = $hoje->dayOfWeek === $horario->dia_semana
            ? $hoje->toDateString()
            : $hoje->next($horario->dia_semana)->toDateString();
        $validated['status'] = 'agendado';

        Agendamento::create($validated);
        $nomeAluno = Aluno::find($validated['aluno_id'])->nome;
        $this->registrarLog('criou', 'Agendamento', "Criou o agendamento para o aluno {$nomeAluno}");

        // Remove o aluno da fila de espera (muda status de 'aguardando' para 'agendado')
        Aluno::find($validated['aluno_id'])
            ->listasEspera()
            ->updateExistingPivot($validated['lista_espera_id'], ['status' => 'agendado']);

        return redirect()->route('agendamentos', ['data' => $validated['data']])
            ->with('success', 'Agendamento criado com sucesso!');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $agendamento = Agendamento::with(['aluno.listasEspera', 'horarioProfissional.profissional', 'listaEspera'])->findOrFail($id);

        $listasEsperaIds = $agendamento->aluno->listasEspera->pluck('id');

        $profissionais = Profissional::whereHas('listasEspera', fn($q) => $q->whereIn('listas_espera.id', $listasEsperaIds))
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        $listasEspera = $agendamento->aluno->listasEspera()->where('ativo', true)->get();

        return view('atendimento.editAgendamento', compact('agendamento', 'profissionais', 'listasEspera'));
    }

    public function profissionaisPorAluno(string $alunoId)
    {
        $aluno = Aluno::findOrFail($alunoId);

        // Apenas listas onde o aluno está aguardando
        $listasDoAluno = $aluno->listasEspera()
            ->where('ativo', true)
            ->wherePivot('status', 'aguardando')
            ->get(['listas_espera.id', 'nome']);

        $listasIds = $listasDoAluno->pluck('id');

        // Profissionais vinculados a essas listas, incluindo quais listas cada um tem
        $profissionais = Profissional::whereHas('listasEspera', fn($q) => $q->whereIn('listas_espera.id', $listasIds))
            ->where('ativo', true)
            ->orderBy('nome')
            ->with(['listasEspera' => fn($q) => $q->whereIn('listas_espera.id', $listasIds)->select('listas_espera.id')])
            ->get(['id', 'nome'])
            ->map(fn($p) => [
                'id'        => $p->id,
                'nome'      => $p->nome,
                'listas_ids' => $p->listasEspera->pluck('id'),
            ]);

        return response()->json([
            'profissionais' => $profissionais,
            'listasEspera'  => $listasDoAluno,
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $agendamento = Agendamento::findOrFail($id);

        $validated = $request->validate([
            'horarios_profissional_id' => 'sometimes|exists:horarios_profissional,id',
            'lista_espera_id'          => 'sometimes|exists:listas_espera,id',
            'status'                   => 'sometimes|in:agendado,realizado,cancelado,falta',
            'observacoes'              => 'nullable|string|max:1000',
        ]);

        // Se o horário mudou, recalcula a data da próxima ocorrência
        if (isset($validated['horarios_profissional_id']) &&
            $validated['horarios_profissional_id'] != $agendamento->horarios_profissional_id) {
            $horario = HorarioProfissional::find($validated['horarios_profissional_id']);
            $hoje    = today();
            $validated['data'] = $hoje->dayOfWeek === $horario->dia_semana
                ? $hoje->toDateString()
                : $hoje->next($horario->dia_semana)->toDateString();
        }

        $agendamento->update($validated);
        $this->registrarLog('editou', 'Agendamento', "Editou o agendamento do aluno {$agendamento->aluno->nome}");

        return redirect()->route('agendamentos')
            ->with('success', 'Agendamento atualizado com sucesso!');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $agendamento = Agendamento::with('aluno')->findOrFail($id);

        // Devolve o aluno para o fim da fila de espera
        $agendamento->aluno
            ->listasEspera()
            ->updateExistingPivot($agendamento->lista_espera_id, [
                'status'       => 'aguardando',
                'data_entrada' => now()->toDateString(),
            ]);

        $agendamento->delete();
        $this->registrarLog('excluiu', 'Agendamento', "Removeu o agendamento do aluno {$agendamento->aluno->nome}");

        return redirect()->route('agendamentos')
            ->with('success', 'Agendamento removido com sucesso!');
    }

    public function relatorioAluno(Request $request, string $alunoId){
        $aluno = Aluno::findOrFail($alunoId);
        $profissionalId = $request->query('profissional_id');

        $agendamentos = Agendamento::with(['horarioProfissional.profissional'])->where('aluno_id', $alunoId)->where('status', 'agendado')->when($profissionalId, function ($q) use ($profissionalId){
            $q->whereHas('horarioProfissional', fn($q2) => $q2->where('profissional_id', $profissionalId));
        })->get()->sortBy('horarioProfissional.dia_semana');

        $profissional = $profissionalId ? \App\Models\Profissional::find($profissionalId) : null;

        return view('atendimento.relatorioAluno', compact('aluno', 'agendamentos', 'profissional'));
    }

    public function relatorioProfissional(string $profissionalId)
    {
        $profissional = Profissional::findOrFail($profissionalId);

        $agendamentos = Agendamento::with(['aluno', 'horarioProfissional'])
            ->where('status', 'agendado')
            ->whereHas('horarioProfissional', fn($q) => $q->where('profissional_id', $profissionalId))
            ->get()
            ->sortBy('horarioProfissional.dia_semana');

        return view('atendimento.relatorioProfissional', compact('profissional', 'agendamentos'));
    }

    public function imprimir(Request $request)
    {
        $modo           = $request->query('modo', 'dia');
        $dataSelecionada = $request->query('data', today()->toDateString());
        $profissionalId = $request->query('profissional_id');
        $dataCarbon     = \Carbon\Carbon::parse($dataSelecionada);
        $diaSemana      = $dataCarbon->dayOfWeek;

        $profissional = $profissionalId ? Profissional::find($profissionalId) : null;

        $query = Agendamento::with(['aluno', 'horarioProfissional.profissional'])
            ->where('status', 'agendado');

        if ($profissionalId) {
            $query->whereHas('horarioProfissional', fn($q) =>
                $q->where('profissional_id', $profissionalId)->where('ativo', true)
            );
        } else {
            $query->whereHas('horarioProfissional', fn($q) => $q->where('ativo', true));
        }

        if ($modo === 'dia') {
            $query->whereHas('horarioProfissional', fn($q) =>
                $q->where('dia_semana', $diaSemana)
            );
        }

        $agendamentos = $query->get()->sortBy([
            ['horarioProfissional.dia_semana', 'asc'],
            ['horarioProfissional.hora_inicio', 'asc'],
        ]);

        $ocorrenciasMes = [];
        if ($modo === 'mes') {
            $inicioMes = $dataCarbon->copy()->startOfMonth();
            $fimMes    = $dataCarbon->copy()->endOfMonth();
            for ($dia = 0; $dia <= 6; $dia++) {
                $count   = 0;
                $current = $inicioMes->copy();
                while ($current->lte($fimMes)) {
                    if ($current->dayOfWeek === $dia) $count++;
                    $current->addDay();
                }
                $ocorrenciasMes[$dia] = $count;
            }
        }

        return view('atendimento.impressaoAgendamentos', compact(
            'agendamentos', 'profissional', 'modo',
            'dataCarbon', 'ocorrenciasMes'
        ));
    }

    public function horarios(Request $request)
    {
        $profissionalId = $request->query('profissional_id');
        $excluirId      = $request->query('excluir_id');
        $alunoId        = $request->query('aluno_id');

        if (!$profissionalId) {
            return response()->json([]);
        }

        // Slots já ocupados pelo profissional
        $ocupados = HorarioProfissional::where('profissional_id', $profissionalId)
            ->whereHas('agendamentos', function ($q) use ($excluirId) {
                $q->where('status', 'agendado');
                if ($excluirId) {
                    $q->where('id', '!=', $excluirId);
                }
            })
            ->pluck('id')
            ->toArray();

        // Slots que conflitam com agendamentos existentes do aluno
        $conflitantes = [];
        if ($alunoId) {
            $horariosDoAluno = HorarioProfissional::whereHas('agendamentos', function ($q) use ($alunoId, $excluirId) {
                $q->where('aluno_id', $alunoId)->where('status', 'agendado');
                if ($excluirId) {
                    $q->where('id', '!=', $excluirId);
                }
            })->get(['dia_semana', 'hora_inicio']);

            if ($horariosDoAluno->isNotEmpty()) {
                $conflitantes = HorarioProfissional::where('profissional_id', $profissionalId)
                    ->where(function ($q) use ($horariosDoAluno) {
                        foreach ($horariosDoAluno as $h) {
                            $q->orWhere(function ($q2) use ($h) {
                                $q2->where('dia_semana', $h->dia_semana)
                                   ->where('hora_inicio', $h->hora_inicio);
                            });
                        }
                    })
                    ->pluck('id')
                    ->toArray();
            }
        }

        $excluirIds = array_unique(array_merge($ocupados, $conflitantes));

        $horarios = HorarioProfissional::where('profissional_id', $profissionalId)
            ->where('ativo', true)
            ->when(!empty($excluirIds), fn($q) => $q->whereNotIn('id', $excluirIds))
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get(['id', 'hora_inicio', 'duracao_minutos', 'dia_semana']);

        return response()->json($horarios);
    }
}
