@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/atendimento/formAtendimento.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/aluno/createAluno.css') }}">
@endpush

@section('content')
<div class="fat-page">

  {{-- ====== BANNER ====== --}}
  <div class="fat-banner">
    <a href="{{ route('atendimento.lancar') }}" class="fat-back">
      <i class="bi bi-arrow-left"></i> Voltar
    </a>
    <div class="fat-banner-divider"></div>
    <div class="fat-banner-info">
      <span class="fat-badge-label">Lançar Atendimento</span>
      <h1 class="fat-aluno-nome">{{ $aluno->nome }}</h1>
    </div>
  </div>

  <div class="fat-content">
    <form action="{{ route('atendimento.store') }}" method="POST" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="aluno_id" value="{{ $aluno->id }}">

      {{-- ====== LINHA 1: Data + Profissional + Presença ====== --}}
      <div class="fat-row-top">

        {{-- Data do atendimento --}}
        <div class="fat-card">
          <div class="fat-card-header">
            <div class="fat-card-icon"><i class="bi bi-calendar-event"></i></div>
            <span class="fat-card-title">Data do atendimento</span>
          </div>
          <div class="fat-card-body">
            <input
              type="date"
              name="data_atendimento"
              class="fat-input"
              value="{{ date('Y-m-d') }}"
              required
            >
          </div>
        </div>

        {{-- Profissional --}}
        <div class="fat-card">
          <div class="fat-card-header">
            <div class="fat-card-icon"><i class="bi bi-person-badge"></i></div>
            <span class="fat-card-title">Profissional responsável</span>
          </div>
          <div class="fat-card-body">
            <select name="profissional_id" class="fat-input" required>
              <option value="">Selecione o profissional...</option>
              @foreach($profissionais as $prof)
                <option value="{{ $prof->id }}">{{ $prof->nome }}{{ $prof->profissao ? ' — ' . $prof->profissao : '' }}</option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- Presença --}}
        <div class="fat-card">
          <div class="fat-card-header">
            <div class="fat-card-icon"><i class="bi bi-person-check"></i></div>
            <span class="fat-card-title">Presença</span>
          </div>
          <div class="fat-card-body">
            <div class="fat-radio-group">
              <label class="fat-radio-opt">
                <input type="radio" name="faltou" value="0" checked onchange="toggleFalta(this)">
                <span class="fat-radio-box">
                  <i class="bi bi-check-circle-fill" style="color:#16a34a;"></i>
                  Presente
                </span>
              </label>
              <label class="fat-radio-opt">
                <input type="radio" name="faltou" value="1" onchange="toggleFalta(this)">
                <span class="fat-radio-box">
                  <i class="bi bi-x-circle-fill" style="color:#dc2626;"></i>
                  Faltou
                </span>
              </label>
            </div>

            <div id="motivo-falta-wrap" class="fat-motivo-wrap" style="display:none;">
              <label class="fat-label">Motivo da falta <span class="fat-required">*</span></label>
              <textarea
                name="motivo_falta"
                id="motivo-falta"
                class="fat-textarea"
                rows="2"
                placeholder="Descreva o motivo da falta..."
              ></textarea>
            </div>
          </div>
        </div>

      </div>

      {{-- ====== LINHA 2: Atividades planejadas (full) ====== --}}
      <div class="fat-card fat-card-full">
        <div class="fat-card-header">
          <div class="fat-card-icon"><i class="bi bi-list-check"></i></div>
          <span class="fat-card-title">Atividades planejadas</span>
        </div>
        <div class="fat-card-body">
          <textarea
            name="atividades_planejadas"
            class="fat-textarea"
            rows="4"
            placeholder="Descreva as atividades que foram planejadas para este atendimento..."
          ></textarea>
        </div>
      </div>

      {{-- ====== LINHA 3: Resumo (full) ====== --}}
      <div class="fat-card fat-card-full">
        <div class="fat-card-header">
          <div class="fat-card-icon"><i class="bi bi-pencil-square"></i></div>
          <span class="fat-card-title">Resumo do atendimento</span>
        </div>
        <div class="fat-card-body">
          <p class="fat-helper">Escreva uma frase curta sobre como foi a sessão. Esse resumo aparece no histórico do aluno.</p>
          <textarea
            name="resumo"
            class="fat-textarea"
            rows="2"
            maxlength="300"
            placeholder="Ex: Sessão produtiva, aluno demonstrou avanço na coordenação e boa disposição durante as atividades."
            oninput="document.getElementById('resumo-contador').textContent = this.value.length"
          ></textarea>
          <small style="color:#6b7280;"><span id="resumo-contador">0</span>/300 caracteres</small>
        </div>
      </div>

      {{-- ====== LINHA 4: Observações (full) ====== --}}
      <div class="fat-card fat-card-full">
        <div class="fat-card-header">
          <div class="fat-card-icon"><i class="bi bi-chat-square-text"></i></div>
          <span class="fat-card-title">Observações do profissional</span>
        </div>
        <div class="fat-card-body">
          <p class="fat-helper">Descreva como foi o atendimento: como o aluno reagiu às atividades, seu comportamento e disposição durante a sessão, avanços observados em relação aos atendimentos anteriores, dificuldades apresentadas e qualquer outro aspecto relevante para o acompanhamento.</p>
          <textarea
            name="observacoes"
            class="fat-textarea"
            rows="8"
            placeholder="Ex: O aluno demonstrou boa receptividade às atividades propostas. Apresentou dificuldade em manter o foco por períodos longos, mas respondeu bem às pausas curtas. Houve avanço na coordenação motora fina em relação à sessão anterior..."
          ></textarea>
        </div>
      </div>

      {{-- ====== LINHA 4: Fichas (full) ====== --}}
      <div class="fat-card fat-card-full">
        <div class="fat-card-header">
          <div class="fat-card-icon"><i class="bi bi-paperclip"></i></div>
          <span class="fat-card-title">Fichas de atendimento</span>
        </div>
        <div class="fat-card-body">
          <div class="documentos-area">
            <label class="documentos-dropzone" id="dropzone" for="fichas">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
              </svg>
              <span class="dropzone-titulo">Clique para selecionar ou arraste os arquivos aqui</span>
              <span class="dropzone-sub">PDF ou imagem • Você pode enviar vários arquivos</span>
            </label>
            <input type="file" id="fichas" name="fichas[]" multiple class="d-none" accept=".pdf,image/*">
            <ul id="listaDocumentos" class="lista-documentos"></ul>
          </div>
        </div>
      </div>

      {{-- ====== RODAPÉ ====== --}}
      <div class="fat-footer">
        <a href="{{ route('atendimento.lancar') }}" class="fat-btn-cancelar">Cancelar</a>
        <button type="submit" class="fat-btn-salvar">
          <i class="bi bi-check-lg"></i> Salvar atendimento
        </button>
      </div>

    </form>
  </div>

</div>
@endsection

@push('scripts')
<script>
  function toggleFalta(radio) {
    const wrap     = document.getElementById('motivo-falta-wrap');
    const textarea = document.getElementById('motivo-falta');
    if (radio.value === '1') {
      wrap.style.display = 'block';
      textarea.required  = true;
    } else {
      wrap.style.display = 'none';
      textarea.required  = false;
      textarea.value     = '';
    }
  }

  var arquivosSelecionados = [];
  var inputDocumentos      = document.getElementById('fichas');
  var dropzone             = document.getElementById('dropzone');

  inputDocumentos.addEventListener('change', function () {
    adicionarArquivos(this.files);
  });

  dropzone.addEventListener('dragover', function (e) {
    e.preventDefault();
    dropzone.classList.add('dragover');
  });

  dropzone.addEventListener('dragleave', function () {
    dropzone.classList.remove('dragover');
  });

  dropzone.addEventListener('drop', function (e) {
    e.preventDefault();
    dropzone.classList.remove('dragover');
    adicionarArquivos(e.dataTransfer.files);
  });

  function adicionarArquivos(files) {
    for (var i = 0; i < files.length; i++) {
      arquivosSelecionados.push(files[i]);
    }
    renderizarLista();
  }

  function renderizarLista() {
    var lista        = document.getElementById('listaDocumentos');
    var dataTransfer = new DataTransfer();
    lista.innerHTML  = '';

    arquivosSelecionados.forEach(function (arquivo, index) {
      dataTransfer.items.add(arquivo);

      var icone   = arquivo.type.startsWith('image/') ? '🖼️' : '📄';
      var tamanho = (arquivo.size / 1024).toFixed(1) + ' KB';

      var item = document.createElement('li');
      item.innerHTML =
        '<div class="doc-info">' +
          '<span class="doc-icone">' + icone + '</span>' +
          '<div>' +
            '<span class="doc-nome">' + arquivo.name + '</span>' +
            '<span class="doc-tamanho">' + tamanho + '</span>' +
          '</div>' +
        '</div>' +
        '<button type="button" class="doc-remover" onclick="removerArquivo(' + index + ')" title="Remover">&times;</button>';
      lista.appendChild(item);
    });

    inputDocumentos.files = dataTransfer.files;
  }

  function removerArquivo(index) {
    arquivosSelecionados.splice(index, 1);
    renderizarLista();
  }
</script>
@endpush
