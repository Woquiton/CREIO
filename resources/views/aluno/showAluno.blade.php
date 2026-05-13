@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/aluno/createAluno.css') }}">
@endpush

@section('content')

<div class="aluno-page">
  <div class="aluno-card">

    {{-- Cabeçalho --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
      <h1 class="aluno-title m-0">INFORMAÇÕES DE ALUNO</h1>
      <div class="d-flex gap-2">
        <a href="{{ route('alunos.index') }}" class="btn btn-soft-secondary" style="font-size:13px; padding:8px 16px;">
          Voltar
        </a>
        <a href="{{ route('alunos.edit', $aluno->id) }}" class="btn btn-soft-primary" style="font-size:13px; padding:8px 16px;">
          Editar
        </a>
        <a href="{{ route('alunos.ficha', $aluno->id) }}" target="_blank" class="btn btn-soft-secondary" style="font-size:13px; padding:8px 16px;">
          Imprimir Ficha
        </a>
        @if($aluno->ativo)
          <button type="button" class="btn btn-soft-danger" style="font-size:13px; padding:8px 16px;"
            onclick="abrirModalDesligamento({{ $aluno->id }}, '{{ addslashes($aluno->nome) }}')">
            Desativar
          </button>
        @else
          <form action="{{ route('alunos.toggle', $aluno->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-success" style="font-size:13px; padding:8px 16px;"
              onclick="return confirm('Deseja reativar {{ addslashes($aluno->nome) }}?')">
              Reativar
            </button>
          </form>
        @endif
      </div>
    </div>

    @if(session('success'))
      <div style="background:#f0fdf4; border:1px solid #86efac; border-radius:8px; padding:12px 16px; margin:16px 0; color:#166534;">
        {{ session('success') }}
      </div>
    @endif

    @if(!$aluno->ativo && $aluno->justificativa_desligamento)
      <div style="background:#fef2f2; border:1px solid #fca5a5; border-radius:8px; padding:14px 18px; margin-bottom:16px;">
        <strong style="color:#b91c1c;">⛔ Aluno desligado</strong>
        <p style="color:#7f1d1d; margin:6px 0 0; font-size:0.9rem;">{{ $aluno->justificativa_desligamento }}</p>
      </div>
    @endif

    {{-- Foto + dados principais --}}
    <div class="foto-row">

      <div class="foto-col">
        <div class="foto-show-circle">
          @if($aluno->foto)
            <img src="{{ asset('storage/' . $aluno->foto) }}" alt="Foto do aluno"
              style="width:100%; height:100%; object-fit:cover; border-radius:50%;"
              onclick="document.getElementById('lightboxBackdrop').classList.add('open')"
              title="Clique para ampliar">
          @else
            <div class="upload-avatar-icon">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
              </svg>
              <span>Sem foto</span>
            </div>
          @endif
        </div>

        <span class="foto-label">
          {{ $aluno->ativo ? '✅ Ativo' : '⛔ Inativo' }}
        </span>
      </div>

      {{-- Lightbox --}}
      @if($aluno->foto)
        <div class="lightbox-backdrop" id="lightboxBackdrop">
          <div class="lightbox-inner">
            <button type="button" class="lightbox-close" id="lightboxClose">&times;</button>
            <img src="{{ asset('storage/' . $aluno->foto) }}" alt="Foto do aluno">
          </div>
        </div>
      @endif

      <div class="foto-fields">
        <div class="row g-3">
          <div class="col-6 col-md-2">
            <label class="form-label">ID</label>
            <input type="text" class="form-control soft-input" value="{{ $aluno->id }}" readonly>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Nome completo</label>
            <input type="text" class="form-control soft-input" value="{{ $aluno->nome }}" readonly>
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label">Data de Nascimento</label>
            <input type="text" class="form-control soft-input"
              value="{{ $aluno->data_nascimento ? \Carbon\Carbon::parse($aluno->data_nascimento)->format('d/m/Y') : '—' }}" readonly>
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label">Sexo</label>
            <div class="soft-radio">
              <div class="form-check">
                <input class="form-check-input" type="radio" disabled {{ $aluno->sexo === 'M' ? 'checked' : '' }}>
                <label class="form-check-label">Masculino</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" disabled {{ $aluno->sexo === 'F' ? 'checked' : '' }}>
                <label class="form-check-label">Feminino</label>
              </div>
            </div>
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label">Celular</label>
            <input type="text" class="form-control soft-input" value="{{ $aluno->celular ?? '—' }}" readonly>
          </div>

        </div>
      </div>

    </div>{{-- fim .foto-row --}}

    {{-- Dados do aluno --}}
    <hr class="block-divider">
    <div class="section-header">
      <div class="section-icon">👤</div>
      <h2 class="section-title">Dados do aluno</h2>
    </div>

    <div class="row g-3">
      <div class="col-12 col-md-5">
        <label class="form-label">Endereço</label>
        <input type="text" class="form-control soft-input" value="{{ $aluno->endereco ?? '—' }}" readonly>
      </div>

      <div class="col-6 col-md-2">
        <label class="form-label">Número</label>
        <input type="text" class="form-control soft-input" value="{{ $aluno->numero ?? '—' }}" readonly>
      </div>

      <div class="col-6 col-md-3">
        <label class="form-label">Bairro</label>
        <input type="text" class="form-control soft-input" value="{{ $aluno->bairro ?? '—' }}" readonly>
      </div>

      <div class="col-6 col-md-2">
        <label class="form-label">CEP</label>
        <input type="text" class="form-control soft-input" value="{{ $aluno->cep ?? '—' }}" readonly>
      </div>

      <div class="col-6 col-md-4">
        <label class="form-label">Cidade</label>
        <input type="text" class="form-control soft-input" value="{{ $aluno->cidade ?? '—' }}" readonly>
      </div>

      <div class="col-12 col-md-4">
        <label class="form-label">Tel. Residencial</label>
        <input type="text" class="form-control soft-input" value="{{ $aluno->tel_residencial ?? '—' }}" readonly>
      </div>

      <div class="col-12 col-md-4">
        <label class="form-label">Escola</label>
        <input type="text" class="form-control soft-input" value="{{ $aluno->escola->nome ?? '—' }}" readonly>
      </div>

      <div class="col-6 col-md-4">
        <label class="form-label">Série</label>
        <input type="text" class="form-control soft-input" value="{{ $aluno->serie ?? '—' }}" readonly>
      </div>

      <div class="col-6 col-md-4">
        <label class="form-label">Turno</label>
        <input type="text" class="form-control soft-input" value="{{ $aluno->turno ?? '—' }}" readonly>
      </div>

      <div class="col-12 col-md-4">
        <label class="form-label">Filiação 1</label>
        <input type="text" class="form-control soft-input" value="{{ $aluno->filiacao1 ?? '—' }}" readonly>
      </div>

      <div class="col-12 col-md-4">
        <label class="form-label">Filiação 2</label>
        <input type="text" class="form-control soft-input" value="{{ $aluno->filiacao2 ?? '—' }}" readonly>
      </div>
    </div>

    {{-- Saúde --}}
    <hr class="block-divider">
    <div class="section-header">
      <div class="section-icon">💊</div>
      <h2 class="section-title">Saúde</h2>
    </div>

    <div class="row g-3">
      <div class="col-12 col-lg-4">
        <label class="form-label">Alérgico a algum medicamento?</label>
        <div class="soft-radio">
          <div class="form-check">
            <input class="form-check-input" type="radio" disabled {{ $aluno->alergico_medicamento ? 'checked' : '' }}>
            <label class="form-check-label">Sim</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" disabled {{ !$aluno->alergico_medicamento ? 'checked' : '' }}>
            <label class="form-check-label">Não</label>
          </div>
        </div>
        @if($aluno->alergico_medicamento && $aluno->alergico_medicamento_qual)
          <label class="form-label mt-2">Qual?</label>
          <textarea class="form-control soft-input soft-textarea" rows="3" readonly>{{ $aluno->alergico_medicamento_qual }}</textarea>
        @endif
      </div>

      <div class="col-12 col-lg-4">
        <label class="form-label">Alérgico a algum alimento?</label>
        <div class="soft-radio">
          <div class="form-check">
            <input class="form-check-input" type="radio" disabled {{ $aluno->alergico_alimento ? 'checked' : '' }}>
            <label class="form-check-label">Sim</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" disabled {{ !$aluno->alergico_alimento ? 'checked' : '' }}>
            <label class="form-check-label">Não</label>
          </div>
        </div>
        @if($aluno->alergico_alimento && $aluno->alergico_alimento_qual)
          <label class="form-label mt-2">Qual?</label>
          <textarea class="form-control soft-input soft-textarea" rows="3" readonly>{{ $aluno->alergico_alimento_qual }}</textarea>
        @endif
      </div>

      <div class="col-12 col-lg-4">
        <label class="form-label">Faz uso de medicação específica?</label>
        <div class="soft-radio">
          <div class="form-check">
            <input class="form-check-input" type="radio" disabled {{ $aluno->usa_medicacao ? 'checked' : '' }}>
            <label class="form-check-label">Sim</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" disabled {{ !$aluno->usa_medicacao ? 'checked' : '' }}>
            <label class="form-check-label">Não</label>
          </div>
        </div>
        @if($aluno->usa_medicacao && $aluno->usa_medicacao_qual)
          <label class="form-label mt-2">Qual?</label>
          <textarea class="form-control soft-input soft-textarea" rows="3" readonly>{{ $aluno->usa_medicacao_qual }}</textarea>
        @endif
      </div>

      <div class="col-12">
        <label class="form-label">Quais profissionais a criança já passa?</label>
        <textarea class="form-control soft-input soft-textarea" rows="3" readonly>{{ $aluno->profissionais_crianca ?? '—' }}</textarea>
      </div>
    </div>

    {{-- Dados do responsável --}}
    <hr class="block-divider">
    <div class="section-header">
      <div class="section-icon">👨‍👩‍👧</div>
      <h2 class="section-title">Dados do Responsável</h2>
    </div>

    <div class="row g-3">
      <div class="col-12 col-md-6">
        <label class="form-label">Nome completo</label>
        <input type="text" class="form-control soft-input" value="{{ $aluno->resp_nome ?? '—' }}" readonly>
      </div>

      <div class="col-12 col-md-6">
        <label class="form-label">Data de Nascimento</label>
        <input type="text" class="form-control soft-input"
          value="{{ $aluno->resp_data_nascimento ? \Carbon\Carbon::parse($aluno->resp_data_nascimento)->format('d/m/Y') : '—' }}" readonly>
      </div>

      <div class="col-12 col-md-4">
        <label class="form-label">RG</label>
        <input type="text" class="form-control soft-input" value="{{ $aluno->resp_rg ?? '—' }}" readonly>
      </div>

      <div class="col-12 col-md-4">
        <label class="form-label">CPF</label>
        <input type="text" class="form-control soft-input" value="{{ $aluno->resp_cpf ?? '—' }}" readonly>
      </div>

      <div class="col-12 col-md-4">
        <label class="form-label">Estado Civil</label>
        <input type="text" class="form-control soft-input" value="{{ $aluno->resp_estado_civil ?? '—' }}" readonly>
      </div>

      <div class="col-12">
        <label class="form-label">Filas de espera</label>
        <div class="chips">
          @forelse($aluno->listasEspera as $lista)
            <label class="chip" style="cursor:default;">
              <input type="checkbox" disabled checked>
              <span>{{ $lista->nome }}</span>
            </label>
          @empty
            <p style="color:#94a3b8; font-size:0.85rem; margin:0;">Nenhuma lista de espera.</p>
          @endforelse
        </div>
      </div>
    </div>

    {{-- Métricas do núcleo --}}
    <div class="metric-section">

      <h2 class="metric-title">📊 Informações para Metrificação do Núcleo</h2>
      <p class="metric-subtitle">Dados utilizados para relatórios estatísticos e planejamento institucional</p>

      <div class="row g-4">

        <div class="col-12">
          <label class="form-label">Tipo de Deficiência</label>
          <div class="chips metric-chips">
            @forelse($aluno->deficiencias as $deficiencia)
              <label class="chip metric-chip" style="cursor:default;">
                <input type="checkbox" disabled checked>
                <span>{{ $deficiencia->nome }}</span>
              </label>
            @empty
              <p style="color:#94a3b8; font-size:0.85rem; margin:0;">Nenhuma deficiência registrada.</p>
            @endforelse
          </div>
        </div>

        <div class="col-12">
          <label class="form-label">Diagnósticos Clínicos</label>
          <div class="chips metric-chips">
            @forelse($aluno->diagnosticos as $diagnostico)
              <label class="chip metric-chip" style="cursor:default;">
                <input type="checkbox" disabled checked>
                <span>{{ $diagnostico->nome }}</span>
              </label>
            @empty
              <p style="color:#94a3b8; font-size:0.85rem; margin:0;">Nenhum diagnóstico registrado.</p>
            @endforelse
          </div>
        </div>

        <div class="col-12 col-md-4">
          <label class="form-label">Grau de Suporte</label>
          <input type="text" class="form-control soft-input" value="{{ $aluno->grau_suporte ?? '—' }}" readonly>
        </div>

        <div class="col-12 col-md-4">
          <label class="form-label">Possui Laudo Médico?</label>
          <input type="text" class="form-control soft-input" value="{{ $aluno->possui_laudo ? 'Sim' : 'Não' }}" readonly>
        </div>

        <div class="col-12 col-md-4">
          <label class="form-label">Origem do Encaminhamento</label>
          <input type="text" class="form-control soft-input"
            value="{{ $aluno->origemEncaminhamento->nome ?? '—' }}" readonly>
        </div>

        <div class="col-12 col-md-4">
          <label class="form-label">Data do Diagnóstico</label>
          <input type="text" class="form-control soft-input"
            value="{{ $aluno->data_diagnostico ? \Carbon\Carbon::parse($aluno->data_diagnostico)->format('d/m/Y') : '—' }}" readonly>
        </div>

      </div>
    </div>

    {{-- Documentos --}}
    <hr class="block-divider">
    <div class="section-header">
      <div class="section-icon">📎</div>
      <h2 class="section-title">Documentos do Aluno</h2>
    </div>

    @forelse($aluno->documentosAluno as $doc)
      @php
        $ext = strtolower(pathinfo($doc->nome_original, PATHINFO_EXTENSION));
        $isPdf = $ext === 'pdf';
      @endphp
      <div class="doc-card">
        <div class="doc-card-icon">
          @if($isPdf)
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
          @else
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            </svg>
          @endif
        </div>
        <div class="doc-card-info">
          <span class="doc-card-nome">{{ $doc->nome_original }}</span>
          <span class="doc-card-ext">{{ strtoupper($ext) }}</span>
        </div>
        <a href="{{ asset('storage/' . $doc->arquivo) }}" target="_blank" class="doc-card-btn" title="Visualizar documento">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          Ver
        </a>
      </div>
    @empty
      <p class="doc-vazio">Nenhum documento anexado.</p>
    @endforelse

    {{-- Histórico de atendimentos --}}
    <hr class="block-divider">
    <div class="section-header" style="justify-content:space-between; flex-wrap:wrap; gap:10px;">
      <div style="display:flex; align-items:center; gap:10px;">
        <div class="section-icon">📋</div>
        <h2 class="section-title">Histórico de atendimentos</h2>
      </div>
      <a href="{{ route('atendimento.form', $aluno->id) }}"
         style="display:inline-flex; align-items:center; gap:6px; font-size:13px; font-weight:700;
                color:#163C25; background:#EAF3EE; border:1px solid #CFE1D6; border-radius:8px;
                padding:8px 14px; text-decoration:none; transition:.2s;">
        <i class="bi bi-clipboard2-plus"></i> Lançar atendimento
      </a>
    </div>

    <form method="GET" action="{{ route('alunos.show', $aluno->id) }}" style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end; margin-bottom:16px;">
      <div>
        <label style="font-size:12px; font-weight:700; color:#64748b; display:block; margin-bottom:4px;">Data início</label>
        <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" style="border:1px solid #CFE1D6; border-radius:8px; padding:7px 10px; font-size:13px;">
      </div>
      <div>
        <label style="font-size:12px; font-weight:700; color:#64748b; display:block; margin-bottom:4px;">Data fim</label>
        <input type="date" name="data_fim" value="{{ request('data_fim') }}" style="border:1px solid #CFE1D6; border-radius:8px; padding:7px 10px; font-size:13px;">
      </div>
      @if($profissionaisDoAluno->isNotEmpty())
      <div>
        <label style="font-size:12px; font-weight:700; color:#64748b; display:block; margin-bottom:4px;">Profissional</label>
        <select name="profissional_id" style="border:1px solid #CFE1D6; border-radius:8px; padding:7px 10px; font-size:13px; background:#fff; min-width:180px;">
          <option value="">Todos</option>
          @foreach($profissionaisDoAluno as $prof)
            <option value="{{ $prof->id }}" {{ request('profissional_id') == $prof->id ? 'selected' : '' }}>
              {{ $prof->nome }}
            </option>
          @endforeach
        </select>
      </div>
      @endif
      <button type="submit" style="background:#163C25; color:#fff; border:none; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:700; cursor:pointer;">Filtrar</button>
      @if(request()->hasAny(['data_inicio', 'data_fim', 'profissional_id']))
        <a href="{{ route('alunos.show', $aluno->id) }}" style="font-size:13px; color:#6b7280; align-self:center;">Limpar filtro</a>
      @endif
    </form>

    @forelse($atendimentos as $at)
      {{-- Card resumo --}}
      <div style="background:#fff; border:1px solid #CFE1D6; border-radius:12px; padding:14px 16px; margin-bottom:10px;
                  display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">

        {{-- Infos principais --}}
        <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; flex:1; min-width:0;">

          {{-- Data --}}
          <div style="background:#EAF3EE; border:1px solid #CFE1D6; border-radius:8px; padding:6px 12px; text-align:center; flex-shrink:0;">
            <div style="font-size:18px; font-weight:900; color:#163C25; line-height:1;">
              {{ \Carbon\Carbon::parse($at->data_atendimento)->format('d') }}
            </div>
            <div style="font-size:10px; font-weight:700; color:#6b7280; text-transform:uppercase;">
              {{ \Carbon\Carbon::parse($at->data_atendimento)->translatedFormat('M Y') }}
            </div>
          </div>

          {{-- Profissional + presença --}}
          <div style="min-width:0;">
            <div style="font-size:13px; font-weight:700; color:#163C25; margin-bottom:4px;">
              <i class="bi bi-person-badge" style="margin-right:4px;"></i>
              {{ $at->profissional?->nome ?? '—' }}
            </div>
            @if($at->faltou)
              <span style="background:#fef2f2; border:1px solid #fca5a5; color:#b91c1c; border-radius:99px; font-size:11px; font-weight:700; padding:2px 10px;">
                <i class="bi bi-x-circle-fill"></i> Faltou
              </span>
            @else
              <span style="background:#f0fdf4; border:1px solid #86efac; color:#166534; border-radius:99px; font-size:11px; font-weight:700; padding:2px 10px;">
                <i class="bi bi-check-circle-fill"></i> Presente
              </span>
            @endif
          </div>

          {{-- Resumo do atendimento --}}
          @if($at->resumo)
            <div style="font-size:12px; color:#6b7280; flex:1; min-width:0;">
              {{ $at->resumo }}
            </div>
          @endif

        </div>

        {{-- Botão ver detalhes --}}
        <button
          type="button"
          style="flex-shrink:0; display:inline-flex; align-items:center; gap:6px; font-size:12px; font-weight:700;
                 color:#163C25; background:#EAF3EE; border:1px solid #CFE1D6; border-radius:8px;
                 padding:7px 14px; cursor:pointer; transition:.2s; white-space:nowrap;"
          data-bs-toggle="modal"
          data-bs-target="#modalAtendimento{{ $at->id }}"
          onmouseover="this.style.background='#163C25';this.style.color='#fff';"
          onmouseout="this.style.background='#EAF3EE';this.style.color='#163C25';"
        >
          <i class="bi bi-eye"></i> Ver detalhes
        </button>
      </div>

      {{-- Modal de detalhes --}}
      <div class="modal fade" id="modalAtendimento{{ $at->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content" style="border:none; border-radius:14px; overflow:hidden;">

            {{-- Header --}}
            <div class="modal-header" style="background:linear-gradient(135deg,#163C25,#2a6042); border:none; padding:18px 24px;">
              <div>
                <div style="font-size:10px; font-weight:700; color:rgba(255,255,255,.6); text-transform:uppercase; letter-spacing:.08em; margin-bottom:2px;">
                  Detalhes do atendimento
                </div>
                <h5 class="modal-title" style="color:#fff; font-weight:900; margin:0; font-size:16px;">
                  {{ \Carbon\Carbon::parse($at->data_atendimento)->translatedFormat('d \d\e F \d\e Y') }}
                </h5>
              </div>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body" style="padding:24px; background:#f8faf9;">

              {{-- Linha: profissional + presença --}}
              <div style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:18px;">
                <div style="flex:1; background:#fff; border:1px solid #e5ede8; border-radius:10px; padding:14px 16px;">
                  <div style="font-size:10px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.06em; margin-bottom:4px;">Profissional</div>
                  <div style="font-size:14px; font-weight:700; color:#163C25;">
                    <i class="bi bi-person-badge me-1"></i>{{ $at->profissional?->nome ?? '—' }}
                  </div>
                  @if($at->profissional?->profissao)
                    <div style="font-size:12px; color:#6b7280; margin-top:2px;">{{ $at->profissional->profissao }}</div>
                  @endif
                </div>
                <div style="background:#fff; border:1px solid #e5ede8; border-radius:10px; padding:14px 16px; min-width:140px; text-align:center;">
                  <div style="font-size:10px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.06em; margin-bottom:6px;">Presença</div>
                  @if($at->faltou)
                    <span style="background:#fef2f2; border:1px solid #fca5a5; color:#b91c1c; border-radius:99px; font-size:12px; font-weight:700; padding:4px 14px;">
                      <i class="bi bi-x-circle-fill"></i> Faltou
                    </span>
                  @else
                    <span style="background:#f0fdf4; border:1px solid #86efac; color:#166534; border-radius:99px; font-size:12px; font-weight:700; padding:4px 14px;">
                      <i class="bi bi-check-circle-fill"></i> Presente
                    </span>
                  @endif
                </div>
              </div>

              {{-- Motivo da falta --}}
              @if($at->faltou && $at->motivo_falta)
                <div style="background:#fef2f2; border-left:3px solid #dc2626; border-radius:0 8px 8px 0; padding:10px 14px; margin-bottom:16px; font-size:13px; color:#7f1d1d;">
                  <strong>Motivo da falta:</strong> {{ $at->motivo_falta }}
                </div>
              @endif

              {{-- Atividades planejadas --}}
              @if($at->atividades_planejadas)
                <div style="background:#fff; border:1px solid #e5ede8; border-radius:10px; padding:14px 16px; margin-bottom:12px;">
                  <div style="font-size:10px; font-weight:700; color:#163C25; text-transform:uppercase; letter-spacing:.06em; margin-bottom:8px;">
                    <i class="bi bi-list-check me-1"></i> Atividades planejadas
                  </div>
                  <p style="margin:0; font-size:13px; color:#1f1f1f; white-space:pre-line; line-height:1.6;">{{ $at->atividades_planejadas }}</p>
                </div>
              @endif

              {{-- Observações --}}
              @if($at->observacoes)
                <div style="background:#fff; border:1px solid #e5ede8; border-radius:10px; padding:14px 16px; margin-bottom:12px;">
                  <div style="font-size:10px; font-weight:700; color:#163C25; text-transform:uppercase; letter-spacing:.06em; margin-bottom:8px;">
                    <i class="bi bi-chat-square-text me-1"></i> Observações do profissional
                  </div>
                  <p style="margin:0; font-size:13px; color:#1f1f1f; white-space:pre-line; line-height:1.6;">{{ $at->observacoes }}</p>
                </div>
              @endif

              {{-- Fichas --}}
              @if($at->documentos->count() > 0)
                <div style="background:#fff; border:1px solid #e5ede8; border-radius:10px; padding:14px 16px;">
                  <div style="font-size:10px; font-weight:700; color:#163C25; text-transform:uppercase; letter-spacing:.06em; margin-bottom:10px;">
                    <i class="bi bi-paperclip me-1"></i> Fichas de atendimento ({{ $at->documentos->count() }})
                  </div>
                  <div style="display:flex; flex-wrap:wrap; gap:8px;">
                    @foreach($at->documentos as $doc)
                      <a href="{{ asset('storage/' . $doc->arquivo) }}" target="_blank"
                         style="display:inline-flex; align-items:center; gap:6px; font-size:12px; font-weight:600;
                                color:#163C25; background:#EAF3EE; border:1px solid #CFE1D6; border-radius:7px;
                                padding:6px 12px; text-decoration:none;">
                        @if(str_contains($doc->tipo_mime, 'pdf'))
                          <i class="bi bi-file-earmark-pdf" style="color:#dc2626;"></i>
                        @else
                          <i class="bi bi-file-earmark-image" style="color:#2563eb;"></i>
                        @endif
                        {{ $doc->nome_original }}
                      </a>
                    @endforeach
                  </div>
                </div>
              @endif

            </div>

            {{-- Footer --}}
            <div class="modal-footer" style="background:#fff; border-top:1px solid #ececec; padding:14px 24px;">
              <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fechar</button>
              <a href="{{ route('atendimento.edit', $at->id) }}" class="btn btn-sm"
                 style="background:#163C25; color:#fff; font-weight:700;">
                <i class="bi bi-pencil me-1"></i> Editar
              </a>
            </div>

          </div>
        </div>
      </div>

    @empty
      <div style="background:#f3f3f3; border-radius:12px; padding:12px; border:1px solid #ececec;">
        <div style="opacity:.7; padding:8px;">Sem atendimentos cadastrados.</div>
      </div>
    @endforelse

  </div>
</div>

@if($aluno->foto)
<script>
  document.getElementById('lightboxClose').addEventListener('click', function () {
    document.getElementById('lightboxBackdrop').classList.remove('open');
  });
  document.getElementById('lightboxBackdrop').addEventListener('click', function (e) {
    if (e.target === this) this.classList.remove('open');
  });
</script>
@endif

{{-- Modal de desligamento --}}
@if($aluno->ativo)
<div class="modal fade" id="modalDesligamento" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Desativar Aluno</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="formDesligamento" action="{{ route('alunos.toggle', $aluno->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        <div class="modal-body">
          <p style="font-weight:600; margin-bottom:16px;">Aluno: {{ $aluno->nome }}</p>
          <div class="mb-3">
            <label class="form-label">Justificativa do desligamento <span style="color:red">*</span></label>
            <textarea name="justificativa" class="form-control" rows="4" required
              placeholder="Descreva o motivo do desligamento..."></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Documento assinado pelos pais (opcional)</label>
            <input type="file" name="documento" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
            <small class="text-muted">PDF ou imagem • máx. 10MB</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger">Confirmar desligamento</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
  function abrirModalDesligamento() {
    new bootstrap.Modal(document.getElementById('modalDesligamento')).show();
  }
</script>
@endif

@endsection
