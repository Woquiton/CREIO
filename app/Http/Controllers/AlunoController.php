<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Deficiencia;
use App\Models\Diagnostico;
use App\Models\ListaEspera;
use App\Models\Escola;
use App\Models\OrigemEncaminhamento;
use Illuminate\Http\Request;
use App\Traits\RegistraLog;
use Illuminate\Support\Facades\Storage;

class AlunoController extends Controller
{
    use RegistraLog;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $escolas = Escola::orderBy('nome')->get();
        $deficiencias = Deficiencia::orderBy('nome')->get();
        $diagnosticos = Diagnostico::orderBy('nome')->get();

        $query = Aluno::with('escola', 'listasEspera', 'deficiencias');

        if ($request->filled('nome')) {
            $query->where('nome', 'like', '%' . $request->nome . '%');
        }

        if ($request->filled('ativo') && $request->ativo !== '') {
            $query->where('ativo', $request->ativo);
        }

        if ($request->filled('escola_id')) {
            $query->where('escola_id', $request->escola_id);
        }

        if ($request->filled('filiacao')) {
            $query->where(function ($q) use ($request) {
                $q->where('filiacao1', 'like', '%' . $request->filiacao . '%')
                    ->orWhere('filiacao2', 'like', '%' . $request->filiacao . '%');
            });
        }

        if ($request->filled('deficiencia_id')) {
            $query->whereHas('deficiencias', function ($q) use ($request) {
                $q->where('deficiencias.id', $request->deficiencia_id);
            });
        }

        if ($request->filled('diagnostico_id')) {
            $query->whereHas('diagnosticos', function ($q) use ($request) {
                $q->where('diagnostico.id', $request->diagnostico_id);
            });
        }

        $alunos = $query->get();

        return view('aluno.index', compact('alunos', 'escolas', 'deficiencias', 'diagnosticos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $escolas = Escola::all();
        $deficiencias = Deficiencia::all();
        $diagnosticos = Diagnostico::all();
        $origens = OrigemEncaminhamento::all();
        $listaEspera = ListaEspera::where('ativo', true)->get();

        return view('aluno.createAluno', compact('escolas', 'deficiencias', 'diagnosticos', 'origens', 'listaEspera'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome'            => 'required|string|max:100',
            'data_nascimento' => 'required|date',
            'sexo'            => 'required|in:M,F',
            'fot'             => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'documentos'      => 'nullable|array',
            'documentos.*'    => 'file|mimes:pdf,jpg, jpeg, png|max:10240',
        ], [
            'nome.required'            => 'O nome do aluno é obrigatório.',
            'data_nascimento.required' => 'A data de nascimento é obrigatória.',
            'sexo.required'            => 'O sexo do aluno é obrigatório.',
        ]);

        $data = $request->except(['deficiencias', 'diagnosticos', 'listasEspera', 'documentos', '_token']);
        $data['possui_laudo'] = $request->boolean('possui_laudo');

        $aluno = Aluno::create($data);
        $this->registrarLog('criou', 'Aluno', "Cadastrou o aluno {$aluno->nome}");

        if ($request->hasFile('foto')) {
            $caminho = $request->file('foto')->store('fotos/alunos', 'public');
            $aluno->update(['foto' => $caminho]);
        }

        $aluno->deficiencias()->sync($request->deficiencias ?? []);
        $aluno->diagnosticos()->sync($request->diagnosticos ?? []);

        $datasEntrada = $request->data_entrada ?? [];
        $listasSync = collect($request->listasEspera ?? [])->mapWithKeys(fn($id) => [
            $id => ['data_entrada' => $datasEntrada[$id] ?? now()->toDateString()],
        ])->toArray();
        $aluno->listasEspera()->sync($listasSync);

        if ($request->hasFile('documentos')) {
            foreach ($request->file('documentos') as $arquivo) {
                $caminho = $arquivo->store('documentos/alunos', 'public');
                $aluno->documentosAluno()->create([
                    'nome_original' => $arquivo->getClientOriginalName(),
                    'arquivo' => $caminho,
                    'tipo_mime' => $arquivo->getMimeType(),
                ]);
            }
        }

        return redirect()->route('alunos.index')->with('success', "Aluno {$aluno->nome} cadastrado com sucesso");
    }

    /**
     * Display the specified resource.
     */
    public function show(Aluno $aluno)
    {
        $aluno->load('escola', 'origemEncaminhamento', 'deficiencias', 'diagnosticos', 'listasEspera', 'documentosAluno');
        $query = $aluno->registrosAtendimento()->with('documentos', 'profissional');

        if (request()->filled('data_inicio')) {
            $query->where('data_atendimento', '>=', request('data_inicio'));
        }

        if (request()->filled('data_fim')) {
            $query->where('data_atendimento', '<=', request('data_fim'));
        }

        $atendimentos = $query->get();
        return view('aluno.showAluno', compact('aluno', 'atendimentos'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Aluno $aluno)
    {
        $escolas = Escola::all();
        $deficiencias = Deficiencia::all();
        $diagnosticos = Diagnostico::all();
        $origens = OrigemEncaminhamento::all();
        $listaEspera = ListaEspera::where('ativo', true)->get();

        $aluno->load('deficiencias', 'diagnosticos', 'listasEspera');

        return view('aluno.editAluno', compact('aluno', 'escolas', 'deficiencias', 'diagnosticos', 'origens', 'listaEspera'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Aluno $aluno)
    {

        $request->validate([
            'foto'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'documentos'   => 'nullable|array',
            'documentos.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $data = $request->except(['deficiencias', 'diagnosticos', 'listasEspera', 'documentos', '_token', '_method']);
        $data['possui_laudo'] = $request->boolean('possui_laudo');


        if ($request->foto_remover) {
            if ($aluno->foto) {
                Storage::disk('public')->delete($aluno->foto);
            }
            $data['foto'] = null;
        }

        if ($request->hasFile('foto')) {
            $caminho = $request->file('foto')->store('fotos/alunos', 'public');
            $data['foto'] = $caminho;
        }

        $aluno->update($data);
        $this->registrarLog('editou', 'Aluno', "Editou o aluno {$aluno->nome}");

        $aluno->deficiencias()->sync($request->deficiencias ?? []);
        $aluno->diagnosticos()->sync($request->diagnosticos ?? []);

        $aluno->load('listasEspera');
        $datasEntrada    = $request->data_entrada ?? [];
        $datasExistentes = $aluno->listasEspera->pluck('pivot.data_entrada', 'id');
        $listasSync = collect($request->listasEspera ?? [])->mapWithKeys(fn($id) => [
            $id => ['data_entrada' => $datasEntrada[$id] ?? $datasExistentes->get($id) ?? now()->toDateString()],
        ])->toArray();
        $aluno->listasEspera()->sync($listasSync);

        if ($request->hasFile('documentos')) {
            foreach ($request->file('documentos') as $arquivo) {
                $caminho = $arquivo->store('documentos/alunos', 'public');
                $aluno->documentosAluno()->create([
                    'nome_original' => $arquivo->getClientOriginalName(),
                    'arquivo'       => $caminho,
                    'tipo_mime'     => $arquivo->getMimeType(),
                ]);
            }
        }

        return redirect()->route('alunos.show', $aluno->id)->with('success', "Aluno {$aluno->nome} atualizado com sucesso!");
    }

    public function ficha(Aluno $aluno)
    {
        $aluno->load('escola', 'deficiencias', 'diagnosticos', 'origemEncaminhamento');
        return view('aluno.fichaAluno', compact('aluno'));
    }


    /**
     * Ativa ou desativa o aluno.
     */
    public function toggle(Request $request, Aluno $aluno)
    {
        if ($aluno->ativo) {
            $request->validate([
                'justificativa' => 'required|string|max:1000',
                'documento'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            ], [
                'justificativa.required' => 'A justificativa de desligamento é obrigatória.',
            ]);

            $aluno->update([
                'ativo'                      => false,
                'justificativa_desligamento' => $request->justificativa,
            ]);

            $this->registrarLog('desativou', 'Aluno', "Desativou o aluno {$aluno->nome}");


            if ($request->hasFile('documento')) {
                $arquivo = $request->file('documento');
                $caminho = $arquivo->store('documentos/alunos', 'public');
                $aluno->documentosAluno()->create([
                    'nome_original' => $arquivo->getClientOriginalName(),
                    'arquivo'       => $caminho,
                    'tipo_mime'     => $arquivo->getMimeType(),
                ]);
            }

            return redirect()->route('alunos.index')->with('success', "Aluno {$aluno->nome} desativado com sucesso!");
        }

        $aluno->update(['ativo' => true, 'justificativa_desligamento' => null]);

        $this->registrarLog('reativou', 'Aluno', "Reativou o aluno {$aluno->nome}");

        return redirect()->route('alunos.index')->with('success', "Aluno {$aluno->nome} reativado com sucesso!");
    }
}
