@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/aluno/createAluno.css') }}">
@endpush

@section('content')

<div class="aluno-page">
  <div class="aluno-card">

    <h1 class="aluno-title">CADASTRO DE NOVO ALUNO</h1>

    @if($errors->any())
      <div style="background:#fef2f2; border:1px solid #fca5a5; border-radius:8px; padding:14px 18px; margin-bottom:20px;">
        <strong style="color:#b91c1c;">Atenção! Corrija os campos abaixo antes de salvar:</strong>
        <ul style="margin:8px 0 0 18px; color:#b91c1c; font-size:0.9rem;">
          @foreach($errors->all() as $erro)
            <li>{{ $erro }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('alunos.store') }}" method="POST" enctype="multipart/form-data">
      @csrf

      <div class="foto-row">

        <div class="foto-col">
          <div class="upload-avatar-circle" id="avatarCircle">

            <div class="upload-avatar-icon" id="avatarIcon">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
              </svg>
              <span>Foto do aluno</span>
            </div>

            <img id="previewFoto" alt="Foto do aluno" style="display:none;">

            <div class="avatar-overlay" id="avatarOverlay" style="display:none;">
              <span class="avatar-overlay-hint">Clique para opções</span>
            </div>

          </div>

          <div class="upload-avatar-badge" id="avatarBadge">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
          </div>

          <span class="foto-label" id="fotoLabel">Clique para adicionar</span>
          <input id="foto" name="foto" type="file" accept="image/*" class="d-none">
        </div>

        {{-- Menu de ações da foto --}}
        <div class="foto-action-menu" id="fotoActionMenu">
          <button type="button" class="foto-action-btn" id="btnVerFoto">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Ver foto
          </button>
          <button type="button" class="foto-action-btn" id="btnTrocarFoto">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            Trocar foto
          </button>
          <button type="button" class="foto-action-btn foto-action-btn--danger" id="btnExcluirFoto">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
            </svg>
            Excluir foto
          </button>
        </div>

        {{-- Modal confirmação exclusão foto --}}
        <div class="confirm-backdrop" id="confirmBackdrop">
          <div class="confirm-modal">
            <div class="confirm-icon">🗑️</div>
            <h3 class="confirm-title">Excluir foto?</h3>
            <p class="confirm-text">Essa ação não pode ser desfeita.<br>Deseja continuar?</p>
            <div class="confirm-btns">
              <button type="button" class="confirm-btn confirm-btn--cancel" id="btnCancelExcluir">Cancelar</button>
              <button type="button" class="confirm-btn confirm-btn--danger" id="btnConfirmExcluir">Sim, excluir</button>
            </div>
          </div>
        </div>

        {{-- Lightbox --}}
        <div class="lightbox-backdrop" id="lightboxBackdrop">
          <div class="lightbox-inner">
            <button type="button" class="lightbox-close" id="lightboxClose">&times;</button>
            <img id="lightboxImg" src="" alt="Foto do aluno">
          </div>
        </div>

        <div class="foto-fields">
          <div class="row g-3">
            <div class="col-12 col-md-8">
              <label class="form-label">Nome completo <span style="color:#e11d48;">*</span></label>
              <input type="text" name="nome" value="{{ old('nome') }}"
                class="form-control soft-input @error('nome') is-invalid @enderror" required>
              @error('nome') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12 col-md-4">
              <label class="form-label">Data de Nascimento <span style="color:#e11d48;">*</span></label>
              <input type="date" name="data_nascimento" value="{{ old('data_nascimento') }}"
                class="form-control soft-input @error('data_nascimento') is-invalid @enderror" required>
              @error('data_nascimento') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12 col-md-4">
              <label class="form-label">Sexo <span style="color:#e11d48;">*</span></label>
              <div class="soft-radio @error('sexo') is-invalid @enderror">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="sexo" id="sexo_m" value="M"
                  {{ old('sexo') === 'M' ? 'checked' : '' }}>
                  <label class="form-check-label" for="sexo_m">Masculino</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="sexo" id="sexo_f" value="F"
                  {{ old('sexo') === 'F' ? 'checked' : '' }}>
                  <label class="form-check-label" for="sexo_f">Feminino</label>
                </div>
              </div>
              @error('sexo') <div style="color:#dc3545; font-size:0.875rem; margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div class="col-12 col-md-4">
              <label class="form-label">Celular</label>
              <input type="text" name="celular" class="form-control soft-input">
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
          <input type="text" name="endereco" class="form-control soft-input">
        </div>

        <div class="col-6 col-md-2">
          <label class="form-label">Número</label>
          <input type="text" name="numero" class="form-control soft-input">
        </div>

        <div class="col-6 col-md-3">
          <label class="form-label">Bairro</label>
          <input type="text" name="bairro" class="form-control soft-input">
        </div>

        <div class="col-6 col-md-2">
          <label class="form-label">CEP</label>
          <input type="text" name="cep" class="form-control soft-input" inputmode="numeric">
        </div>

        <div class="col-6 col-md-4">
          <label class="form-label">Cidade</label>
          <input type="text" name="cidade" class="form-control soft-input">
        </div>

        <div class="col-12 col-md-4">
          <label class="form-label">Tel. Residencial</label>
          <input type="text" name="tel_residencial" class="form-control soft-input">
        </div>

        <div class="col-12 col-md-4">
          <label class="form-label">Escola</label>
          <select name="escola_id" class="form-select soft-input">
            <option value="">Selecione</option>
            @foreach($escolas as $escola)
              <option value="{{ $escola->id }}">{{ $escola->nome }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-6 col-md-4">
          <label class="form-label">Série</label>
          <input type="text" name="serie" class="form-control soft-input">
        </div>

        <div class="col-6 col-md-4">
          <label class="form-label">Turno</label>
          <select name="turno" class="form-select soft-input">
            <option value="" selected>Selecione</option>
            <option value="Matutino">Matutino</option>
            <option value="Vespertino">Vespertino</option>
            <option value="Noturno">Noturno</option>
            <option value="Integral">Integral</option>
          </select>
        </div>

        <div class="col-12 col-md-4">
          <label class="form-label">Filiação 1</label>
          <input type="text" name="filiacao1" class="form-control soft-input">
        </div>

        <div class="col-12 col-md-4">
          <label class="form-label">Filiação 2</label>
          <input type="text" name="filiacao2" class="form-control soft-input">
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
              <input class="form-check-input" type="radio" name="alergico_medicamento" id="am_sim" value="1"
                onchange="toggleQual('am_qual')">
              <label class="form-check-label" for="am_sim">Sim</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="alergico_medicamento" id="am_nao" value="0"
                onchange="toggleQual('am_qual')">
              <label class="form-check-label" for="am_nao">Não</label>
            </div>
          </div>
          <div id="am_qual" style="display:none;">
            <label class="form-label mt-2">Qual?</label>
            <textarea name="alergico_medicamento_qual" class="form-control soft-input soft-textarea" rows="3"></textarea>
          </div>
        </div>

        <div class="col-12 col-lg-4">
          <label class="form-label">Alérgico a algum alimento?</label>
          <div class="soft-radio">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="alergico_alimento" id="aa_sim" value="1"
                onchange="toggleQual('aa_qual')">
              <label class="form-check-label" for="aa_sim">Sim</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="alergico_alimento" id="aa_nao" value="0"
                onchange="toggleQual('aa_qual')">
              <label class="form-check-label" for="aa_nao">Não</label>
            </div>
          </div>
          <div id="aa_qual" style="display:none;">
            <label class="form-label mt-2">Qual?</label>
            <textarea name="alergico_alimento_qual" class="form-control soft-input soft-textarea" rows="3"></textarea>
          </div>
        </div>

        <div class="col-12 col-lg-4">
          <label class="form-label">Faz uso de medicação específica?</label>
          <div class="soft-radio">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="usa_medicacao" id="um_sim" value="1"
                onchange="toggleQual('um_qual')">
              <label class="form-check-label" for="um_sim">Sim</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="usa_medicacao" id="um_nao" value="0"
                onchange="toggleQual('um_qual')">
              <label class="form-check-label" for="um_nao">Não</label>
            </div>
          </div>
          <div id="um_qual" style="display:none;">
            <label class="form-label mt-2">Qual?</label>
            <textarea name="usa_medicacao_qual" class="form-control soft-input soft-textarea" rows="3"></textarea>
          </div>
        </div>

        <div class="col-12">
          <label class="form-label">Quais profissionais a criança já passa?</label>
          <textarea name="profissionais_crianca" class="form-control soft-input soft-textarea" rows="3"></textarea>
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
          <input type="text" name="resp_nome" class="form-control soft-input">
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label">Data de Nascimento</label>
          <input type="date" name="resp_data_nascimento" class="form-control soft-input">
        </div>

        <div class="col-12 col-md-4">
          <label class="form-label">RG</label>
          <input type="text" name="resp_rg" class="form-control soft-input">
        </div>

        <div class="col-12 col-md-4">
          <label class="form-label">CPF</label>
          <input type="text" name="resp_cpf" class="form-control soft-input">
        </div>

        <div class="col-12 col-md-4">
          <label class="form-label">Estado Civil</label>
          <input type="text" name="resp_estado_civil" class="form-control soft-input">
        </div>

        <div class="col-12">
          <label class="form-label">Filas de espera que o aluno será inserido</label>
          @forelse($listaEspera as $lista)
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:8px;">
              <label class="chip" style="margin:0;">
                <input type="checkbox" name="listasEspera[]" value="{{ $lista->id }}"
                  onchange="toggleDataEntrada({{ $lista->id }}, this.checked)">
                <span>{{ $lista->nome }}</span>
              </label>
              <input type="date" name="data_entrada[{{ $lista->id }}]"
                id="data_entrada_{{ $lista->id }}"
                class="form-control soft-input"
                style="max-width:170px; display:none;"
                disabled>
            </div>
          @empty
            <p style="color:#94a3b8; font-size:0.85rem;">Nenhuma lista de espera ativa cadastrada.</p>
          @endforelse
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
              @foreach($deficiencias as $deficiencia)
                <label class="chip metric-chip">
                  <input type="checkbox" name="deficiencias[]" value="{{ $deficiencia->id }}">
                  <span>{{ $deficiencia->nome }}</span>
                </label>
              @endforeach
            </div>
          </div>

          <div class="col-12">
            <label class="form-label">Diagnósticos Clínicos</label>
            <div class="chips metric-chips">
              @foreach($diagnosticos as $diagnostico)
                <label class="chip metric-chip">
                  <input type="checkbox" name="diagnosticos[]" value="{{ $diagnostico->id }}">
                  <span>{{ $diagnostico->nome }}</span>
                </label>
              @endforeach
            </div>
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label">Grau de Suporte</label>
            <select name="grau_suporte" class="form-select soft-input">
              <option value="">Selecione</option>
              <option value="Nível 1">Nível 1 (Leve)</option>
              <option value="Nível 2">Nível 2 (Moderado)</option>
              <option value="Nível 3">Nível 3 (Intenso)</option>
            </select>
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label">Possui Laudo Médico?</label>
            <select name="possui_laudo" class="form-select soft-input">
              <option value="">Selecione</option>
              <option value="1">Sim</option>
              <option value="0">Não</option>
            </select>
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label">Origem do Encaminhamento</label>
            <select name="origem_encaminhamento_id" class="form-select soft-input">
              <option value="">Selecione</option>
              @foreach($origens as $origem)
                <option value="{{ $origem->id }}">{{ $origem->nome }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label">Data do Diagnóstico</label>
            <input type="date" name="data_diagnostico" class="form-control soft-input">
          </div>

        </div>{{-- fim .row métricas --}}
      </div>{{-- fim .metric-section --}}

      {{-- Anexar documentos --}}
      <hr class="block-divider">
      <div class="section-header">
        <div class="section-icon">📎</div>
        <h2 class="section-title">Documentos do Aluno</h2>
      </div>

      <div class="documentos-area">
        <label class="documentos-dropzone" id="dropzone" for="documentos">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
          </svg>
          <span class="dropzone-titulo">Clique para selecionar ou arraste os arquivos aqui</span>
          <span class="dropzone-sub">PDF ou imagem • Você pode enviar vários arquivos</span>
        </label>
        <input type="file" id="documentos" name="documentos[]" multiple class="d-none" accept=".pdf,image/*">

        <ul id="listaDocumentos" class="lista-documentos"></ul>
      </div>

      {{-- Botões --}}
      <div class="mt-4 d-flex justify-content-end gap-2">
        <a href="{{ route('alunos.index') }}" class="btn btn-soft-secondary">Cancelar</a>
        <button type="submit" class="btn btn-soft-primary">Salvar</button>
      </div>

    </form>
  </div>
</div>

<script>
  function toggleDataEntrada(listaId, checked) {
    const input = document.getElementById('data_entrada_' + listaId);
    input.style.display = checked ? 'inline-block' : 'none';
    input.disabled = !checked;
    if (!checked) input.value = '';
  }

  // ── Campos condicionais (Sim/Não) ─────────────────────────────
  function toggleQual(divId) {
    var radio = document.querySelector('input[onchange*="' + divId + '"]:checked');
    document.getElementById(divId).style.display =
      (radio && radio.value === '1') ? 'block' : 'none';
  }

  // ── Foto ──────────────────────────────────────────────────────
  var fotoAtual      = null;
  var inputFoto      = document.getElementById('foto');
  var previewFoto    = document.getElementById('previewFoto');
  var avatarIcon     = document.getElementById('avatarIcon');
  var avatarOverlay  = document.getElementById('avatarOverlay');
  var avatarBadge    = document.getElementById('avatarBadge');
  var avatarCircle   = document.getElementById('avatarCircle');
  var fotoLabel      = document.getElementById('fotoLabel');
  var fotoActionMenu = document.getElementById('fotoActionMenu');

  avatarCircle.addEventListener('click', function (e) {
    if (fotoAtual) {
      e.stopPropagation();
      posicionarMenu();
      fotoActionMenu.classList.toggle('open');
      return;
    }
    inputFoto.click();
  });

  document.addEventListener('click', function () {
    fotoActionMenu.classList.remove('open');
  });

  fotoActionMenu.addEventListener('click', function (e) {
    e.stopPropagation();
  });

  function posicionarMenu() {
    var rect = avatarCircle.getBoundingClientRect();
    fotoActionMenu.style.top  = (rect.bottom + window.scrollY + 8) + 'px';
    fotoActionMenu.style.left = (rect.left  + window.scrollX)      + 'px';
  }

  inputFoto.addEventListener('change', function () {
    var file = this.files && this.files[0];
    if (!file) return;
    aplicarFoto(URL.createObjectURL(file));
    fotoActionMenu.classList.remove('open');
  });

  function aplicarFoto(url) {
    fotoAtual                   = url;
    previewFoto.src             = url;
    previewFoto.style.display   = 'block';
    avatarIcon.style.display    = 'none';
    avatarOverlay.style.display = 'flex';
    avatarBadge.style.display   = 'none';
    fotoLabel.textContent       = 'Clique na foto para opções';
    document.getElementById('lightboxImg').src = url;
  }

  document.getElementById('btnVerFoto').addEventListener('click', function () {
    fotoActionMenu.classList.remove('open');
    document.getElementById('lightboxBackdrop').classList.add('open');
  });

  document.getElementById('lightboxClose').addEventListener('click', fecharLightbox);
  document.getElementById('lightboxBackdrop').addEventListener('click', function (e) {
    if (e.target === this) fecharLightbox();
  });

  function fecharLightbox() {
    document.getElementById('lightboxBackdrop').classList.remove('open');
  }

  document.getElementById('btnTrocarFoto').addEventListener('click', function () {
    fotoActionMenu.classList.remove('open');
    inputFoto.value = '';
    inputFoto.click();
  });

  document.getElementById('btnExcluirFoto').addEventListener('click', function () {
    fotoActionMenu.classList.remove('open');
    document.getElementById('confirmBackdrop').classList.add('open');
  });

  document.getElementById('btnCancelExcluir').addEventListener('click', function () {
    document.getElementById('confirmBackdrop').classList.remove('open');
  });

  document.getElementById('confirmBackdrop').addEventListener('click', function (e) {
    if (e.target === this) document.getElementById('confirmBackdrop').classList.remove('open');
  });

  document.getElementById('btnConfirmExcluir').addEventListener('click', function () {
    fotoAtual                   = null;
    previewFoto.src             = '';
    previewFoto.style.display   = 'none';
    avatarIcon.style.display    = 'flex';
    avatarOverlay.style.display = 'none';
    avatarBadge.style.display   = 'flex';
    fotoLabel.textContent       = 'Clique para adicionar';
    inputFoto.value             = '';
    document.getElementById('lightboxImg').src = '';
    document.getElementById('confirmBackdrop').classList.remove('open');
  });

  // ── Documentos (drag & drop + clique) ────────────────────────
  var arquivosSelecionados = [];
  var inputDocumentos      = document.getElementById('documentos');
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

      var icone = arquivo.type.startsWith('image/') ? '🖼️' : '📄';
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
        '<button type="button" class="doc-remover" onclick="removerArquivo(' + index + ')" title="Remover">' +
          '&times;' +
        '</button>';
      lista.appendChild(item);
    });

    inputDocumentos.files = dataTransfer.files;
  }

  function removerArquivo(index) {
    arquivosSelecionados.splice(index, 1);
    renderizarLista();
  }
</script>

@endsection
