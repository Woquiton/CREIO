<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Profissional;
use App\Models\RegistroAtendimento;
use Illuminate\Http\Request;
use App\Traits\RegistraLog;

class RegistroAtendimentoController extends Controller
{
    use RegistraLog;
    public function index(Request $request)
    {
        $q = $request->input('q');
        $profissionalVinculado = auth()->user()->profissional;

        $alunos = Aluno::where('ativo', true)
            ->when($profissionalVinculado, fn($query) => $query->whereHas('agendamentos', fn($q2) =>
                $q2->whereHas('horarioProfissional', fn($q3) =>
                    $q3->where('profissional_id', $profissionalVinculado->id)
                )->where('status', 'agendado')
            ))
            ->when($q, fn($query) => $query->where('nome', 'like', "%$q%"))
            ->orderBy('nome')
            ->with('escola')
            ->get();

        return view('atendimento.lancarAtendimento', compact('alunos'));
    }

    public function create($alunoId)
    {
        $aluno = Aluno::findOrFail($alunoId);
        $profissionalVinculado = auth()->user()->profissional;

        $profissionais = $profissionalVinculado
            ? collect([$profissionalVinculado])
            : Profissional::where('ativo', true)->orderBy('nome')->get();

        return view('atendimento.formAtendimento', compact('aluno', 'profissionais'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'aluno_id'              => 'required|exists:alunos,id',
            'profissional_id'       => 'required|exists:profissionais,id',
            'data_atendimento'      => 'required|date',
            'atividades_planejadas' => 'nullable|string',
            'faltou'                => 'required|boolean',
            'motivo_falta'          => 'nullable|string|required_if:faltou,1',
            'resumo'                => 'nullable|string|max:300',
            'observacoes'           => 'nullable|string',
            'fichas.*'              => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'profissional_id.required' => 'O profissional que realizou o atendimento é obrigatório.',
            'motivo_falta.required_if' => 'O motivo da falta é obrigatório quando o aluno faltou.',
        ]);

        $registro = RegistroAtendimento::create([
            'aluno_id'              => $request->aluno_id,
            'profissional_id'       => $request->profissional_id,
            'data_atendimento'      => $request->data_atendimento,
            'atividades_planejadas' => $request->atividades_planejadas,
            'faltou'                => $request->boolean('faltou'),
            'motivo_falta'          => $request->boolean('faltou') ? $request->motivo_falta : null,
            'resumo'                => $request->resumo,
            'observacoes'           => $request->observacoes,
        ]);

        if ($request->hasFile('fichas')) {
            foreach ($request->file('fichas') as $arquivo) {
                $caminho = $arquivo->store('atendimentos', 'public');
                $registro->documentos()->create([
                    'nome_original' => $arquivo->getClientOriginalName(),
                    'arquivo'       => $caminho,
                    'tipo_mime'     => $arquivo->getMimeType(),
                ]);
            }
        }

        $nomeAluno = Aluno::find($registro->aluno_id)->nome;
        $this->registrarLog('criou', 'Atendimento', "Lançou atendimento para o aluno {$nomeAluno}");

        return redirect()
            ->route('alunos.show', $registro->aluno_id)
            ->with('success', 'Atendimento lançado com sucesso!');
    }

    public function edit($id)
    {
        $registro      = RegistroAtendimento::with('documentos', 'aluno')->findOrFail($id);
        $profissionais = Profissional::where('ativo', true)->orderBy('nome')->get();

        return view('atendimento.editAtendimento', compact('registro', 'profissionais'));
    }

    public function update(Request $request, $id)
    {
        $registro = RegistroAtendimento::findOrFail($id);

        $request->validate([
            'profissional_id'       => 'required|exists:profissionais,id',
            'data_atendimento'      => 'required|date',
            'atividades_planejadas' => 'nullable|string',
            'faltou'                => 'required|boolean',
            'motivo_falta'          => 'nullable|string|required_if:faltou,1',
            'resumo'                => 'nullable|string|max:300',
            'observacoes'           => 'nullable|string',
            'fichas.*'              => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'profissional_id.required' => 'O profissional que realizou o atendimento é obrigatório.',
            'motivo_falta.required_if' => 'O motivo da falta é obrigatório quando o aluno faltou.',
        ]);

        $registro->update([
            'profissional_id'       => $request->profissional_id,
            'data_atendimento'      => $request->data_atendimento,
            'atividades_planejadas' => $request->atividades_planejadas,
            'faltou'                => $request->boolean('faltou'),
            'motivo_falta'          => $request->boolean('faltou') ? $request->motivo_falta : null,
            'resumo'                => $request->resumo,
            'observacoes'           => $request->observacoes,
        ]);

        if ($request->hasFile('fichas')) {
            foreach ($request->file('fichas') as $arquivo) {
                $caminho = $arquivo->store('atendimentos', 'public');
                $registro->documentos()->create([
                    'nome_original' => $arquivo->getClientOriginalName(),
                    'arquivo'       => $caminho,
                    'tipo_mime'     => $arquivo->getMimeType(),
                ]);
            }
        }

        $this->registrarLog('editou', 'Atendimento', "Editou atendimento do aluno {$registro->aluno->nome}");

        return redirect()
            ->route('alunos.show', $registro->aluno_id)
            ->with('success', 'Atendimento atualizado com sucesso!');
    }

    public function destroyDocumento($id)
    {
        $doc = \App\Models\DocumentoAtendimento::findOrFail($id);

        abort_unless(\App\Models\RegistroAtendimento::where('id', $doc->registro_atendimento_id)->exists(), 403);

        \Illuminate\Support\Facades\Storage::disk('public')->delete($doc->arquivo);

        $nomeAluno = $doc->registroAtendimento->aluno->nome ?? 'desconhecido';
        $this->registrarLog('excluiu', 'Documento Atendimento', "Removeu o documento {$doc->nome_original} do aluno {$nomeAluno}");

        $doc->delete();

        return back()->with('success', 'Documento removido.');
    }
}
