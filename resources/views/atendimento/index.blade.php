@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin/crud.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/atendimento/index.css') }}">
@endpush

@section('content')

@php
  $dataCarbon   = \Carbon\Carbon::parse($dataSelecionada);
  $inicioSemana = $dataCarbon->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
  $fimSemana    = $inicioSemana->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);

  $diasSemana = [];
  for ($i = 0; $i < 7; $i++) {
    $diasSemana[] = $inicioSemana->copy()->addDays($i);
  }

  $semanaAnterior = $inicioSemana->copy()->subDays(1)->toDateString();
  $proximaSemana  = $fimSemana->copy()->addDays(1)->toDateString();

  $inicioMes  = $dataCarbon->copy()->startOfMonth();
  $mesAnterior  = $inicioMes->copy()->subMonth()->toDateString();
  $proximoMes   = $inicioMes->copy()->addMonth()->toDateString();

  $nomeDia = [
    'Monday'    => 'Segunda-feira',
    'Tuesday'   => 'Terça-feira',
    'Wednesday' => 'Quarta-feira',
    'Thursday'  => 'Quinta-feira',
    'Friday'    => 'Sexta-feira',
    'Saturday'  => 'Sábado',
    'Sunday'    => 'Domingo',
  ];

  $diaSemanaAbrev = [
    'Monday'    => 'Seg',
    'Tuesday'   => 'Ter',
    'Wednesday' => 'Qua',
    'Thursday'  => 'Qui',
    'Friday'    => 'Sex',
    'Saturday'  => 'Sáb',
    'Sunday'    => 'Dom',
  ];

  $nomeDiaPorNumero = [
    0 => 'Domingo',
    1 => 'Segunda-feira',
    2 => 'Terça-feira',
    3 => 'Quarta-feira',
    4 => 'Quinta-feira',
    5 => 'Sexta-feira',
    6 => 'Sábado',
  ];

  $meses = [
    1  => 'janeiro', 2  => 'fevereiro', 3  => 'março',
    4  => 'abril',   5  => 'maio',      6  => 'junho',
    7  => 'julho',   8  => 'agosto',    9  => 'setembro',
    10 => 'outubro', 11 => 'novembro',  12 => 'dezembro',
  ];

  $nomeDiaFormatado = $nomeDia[$dataCarbon->format('l')]
    . ', ' . $dataCarbon->day
    . ' de ' . $meses[(int)$dataCarbon->month]
    . ' de ' . $dataCarbon->year;

  $nomeMesFormatado = ucfirst($meses[(int)$dataCarbon->month]) . ' de ' . $dataCarbon->year;

  $filtrosAtivos = array_filter(['profissional_id' => $profissionalId, 'aluno_id' => $alunoId]);
@endphp

<div class="aluno-page">
<div class="aluno-card">
<div class="ag-page">

  {{-- ===== CABEÇALHO ===== --}}
  <div class="ag-header">
    <h1 class="ag-title">Agendamentos</h1>
    @if(!$profissionalFixo && auth()->user()->temPermissao('agendamentos.gerenciar'))
      <a href="{{ route('agendamentos.create') }}" class="ag-btn-novo">
        <i class="bi bi-plus-lg"></i> Novo agendamento
      </a>
    @endif
  </div>

  {{-- ===== FILTROS ===== --}}
  <div class="ag-filter-bar">
    <form method="GET" action="{{ route('agendamentos') }}" class="ag-filter-form">
      <input type="hidden" name="data" value="{{ $dataSelecionada }}">
      <input type="hidden" name="modo" value="{{ $modo }}">

      @if($profissionalFixo)
        <input type="hidden" name="profissional_id" value="{{ $profissionalId }}">
        <span class="ag-filter-label">
          <i class="bi bi-person-badge"></i> {{ $profissionais->firstWhere('id', $profissionalId)?->nome }}
        </span>
      @else
        <label class="ag-filter-label" for="profissional_id">
          <i class="bi bi-person-badge"></i> Profissional
        </label>
        <select name="profissional_id" id="profissional_id" class="ag-filter-select" onchange="this.form.submit()">
          <option value="">Todos</option>
          @foreach($profissionais as $prof)
            <option value="{{ $prof->id }}" {{ $profissionalId == $prof->id ? 'selected' : '' }}>
              {{ $prof->nome }}
            </option>
          @endforeach
        </select>
      @endif

      <label class="ag-filter-label" for="aluno_id" style="margin-left:12px;">
        <i class="bi bi-person"></i> Aluno
      </label>
      <select name="aluno_id" id="aluno_id" class="ag-filter-select" onchange="this.form.submit()">
        <option value="">Todos</option>
        @foreach($alunos as $aluno)
          <option value="{{ $aluno->id }}" {{ $alunoId == $aluno->id ? 'selected' : '' }}>
            {{ $aluno->nome }}
          </option>
        @endforeach
      </select>

      @if($alunoId || $profissionalId)
        @if($alunoId)
          <a href="{{ route('agendamentos.relatorio', ['alunoId' => $alunoId, 'profissional_id' => $profissionalId]) }}"
             target="_blank" class="ag-btn-novo" style="margin-left:auto;">
            <i class="bi bi-printer"></i> Imprimir horários
          </a>
        @else
          <a href="{{ route('agendamentos.relatorio.profissional', $profissionalId) }}"
             target="_blank" class="ag-btn-novo" style="margin-left:auto;">
            <i class="bi bi-printer"></i> Imprimir horários
          </a>
        @endif
      @endif

    </form>
  </div>

  {{-- ===== TOGGLE DIA / SEMANA / MÊS ===== --}}
  <div style="display:flex; align-items:center; gap:6px; margin: 12px 0;">
    @foreach(['dia' => 'Dia', 'semana' => 'Semana', 'mes' => 'Mês'] as $m => $label)
      @php
        $estiloModo = 'padding:6px 18px; border-radius:999px; font-size:0.85rem; font-weight:600; text-decoration:none; '
          . ($modo === $m ? 'background:#1e293b; color:#fff;' : 'background:#f1f5f9; color:#475569;');
      @endphp
      <a href="{{ route('agendamentos', array_filter(array_merge($filtrosAtivos, ['data' => $dataSelecionada, 'modo' => $m]))) }}"
         style="{{ $estiloModo }}">
        {{ $label }}
      </a>
    @endforeach

    <a href="{{ route('agendamentos.imprimir', array_filter(array_merge($filtrosAtivos, ['data' => $dataSelecionada, 'modo' => $modo]))) }}"
       target="_blank"
       style="margin-left:auto; padding:6px 16px; border-radius:999px; font-size:0.85rem; font-weight:600;
              text-decoration:none; background:#f1f5f9; color:#475569; display:inline-flex; align-items:center; gap:6px;">
      <i class="bi bi-printer"></i> Imprimir
    </a>
  </div>

  {{-- ===== NAVEGAÇÃO (semana para modo dia/semana, mês para modo mes) ===== --}}
  @if($modo !== 'mes')
    <div class="ag-semana-wrap">
      <a href="{{ route('agendamentos', array_filter(array_merge($filtrosAtivos, ['data' => $semanaAnterior, 'modo' => $modo]))) }}"
         class="ag-semana-nav" title="Semana anterior">
        <i class="bi bi-chevron-left"></i>
      </a>
      <div class="ag-dias">
        @foreach($diasSemana as $dia)
          @php
            $isHoje        = $dia->isToday();
            $isSelecionado = $dia->toDateString() === $dataSelecionada;
            $classes = 'ag-dia';
            if ($isSelecionado)  $classes .= ' ag-dia--ativo';
            elseif ($isHoje)     $classes .= ' ag-dia--hoje';
          @endphp
          <a href="{{ route('agendamentos', array_filter(array_merge($filtrosAtivos, ['data' => $dia->toDateString(), 'modo' => $modo]))) }}"
             class="{{ $classes }}">
            <span class="ag-dia-nome">{{ $diaSemanaAbrev[$dia->format('l')] }}</span>
            <span class="ag-dia-num">{{ $dia->day }}</span>
            @if($isHoje && !$isSelecionado)
              <span class="ag-dia-dot"></span>
            @endif
          </a>
        @endforeach
      </div>
      <a href="{{ route('agendamentos', array_filter(array_merge($filtrosAtivos, ['data' => $proximaSemana, 'modo' => $modo]))) }}"
         class="ag-semana-nav" title="Próxima semana">
        <i class="bi bi-chevron-right"></i>
      </a>
    </div>
  @else
    <div class="ag-semana-wrap" style="justify-content:center; gap:24px;">
      <a href="{{ route('agendamentos', array_filter(array_merge($filtrosAtivos, ['data' => $mesAnterior, 'modo' => 'mes']))) }}"
         class="ag-semana-nav" title="Mês anterior">
        <i class="bi bi-chevron-left"></i>
      </a>
      <span style="font-weight:700; font-size:1rem; color:#1e293b;">{{ $nomeMesFormatado }}</span>
      <a href="{{ route('agendamentos', array_filter(array_merge($filtrosAtivos, ['data' => $proximoMes, 'modo' => 'mes']))) }}"
         class="ag-semana-nav" title="Próximo mês">
        <i class="bi bi-chevron-right"></i>
      </a>
    </div>
  @endif

  {{-- ===== CONTEÚDO: DIA ===== --}}
  @if($modo === 'dia')
    <div class="ag-lista-wrap">
      <div class="ag-lista-header">
        <span class="ag-lista-data">
          <i class="bi bi-calendar3"></i>
          {{ $nomeDiaFormatado }}
        </span>
        <span class="ag-lista-total">
          {{ $agendamentos->count() }} agendamento{{ $agendamentos->count() !== 1 ? 's' : '' }}
        </span>
      </div>

      @if($agendamentos->isEmpty())
        <div class="ag-vazio">
          <i class="bi bi-calendar-x ag-vazio-icon"></i>
          <p>Nenhum agendamento para este dia.</p>
          @if(!$profissionalFixo && auth()->user()->temPermissao('agendamentos.gerenciar'))
            <a href="{{ route('agendamentos.create') }}" class="ag-btn-novo ag-btn-novo--outline">
              <i class="bi bi-plus-lg"></i> Criar agendamento
            </a>
          @endif
        </div>
      @else
        <div class="ag-cards">
          @foreach($agendamentos as $ag)
            @include('atendimento._card', ['ag' => $ag])
          @endforeach
        </div>
      @endif
    </div>
  @endif

  {{-- ===== CONTEÚDO: SEMANA ===== --}}
  @if($modo === 'semana')
    @php $porDia = $agendamentos->groupBy('horarioProfissional.dia_semana'); @endphp

    <div class="ag-lista-wrap">
      <div class="ag-lista-header">
        <span class="ag-lista-data">
          <i class="bi bi-calendar-week"></i>
          {{ $inicioSemana->format('d/m') }} – {{ $fimSemana->format('d/m/Y') }}
        </span>
        <span class="ag-lista-total">
          {{ $agendamentos->count() }} agendamento{{ $agendamentos->count() !== 1 ? 's' : '' }} na semana
        </span>
      </div>

      @if($agendamentos->isEmpty())
        <div class="ag-vazio">
          <i class="bi bi-calendar-x ag-vazio-icon"></i>
          <p>Nenhum agendamento para esta semana.</p>
        </div>
      @else
        @foreach([1,2,3,4,5,6,0] as $numDia)
          @if($porDia->has($numDia))
            @php $agsNoDia = $porDia[$numDia]; @endphp
            <div style="margin-top:16px;">
              <div class="ag-lista-header" style="background:#f8fafc; border-radius:8px 8px 0 0;">
                <span style="font-weight:700; color:#1e293b;">
                  {{ $nomeDiaPorNumero[$numDia] }}
                </span>
                <span class="ag-lista-total">
                  {{ $agsNoDia->count() }} aluno{{ $agsNoDia->count() !== 1 ? 's' : '' }}
                </span>
              </div>
              <div class="ag-cards" style="padding-top:0;">
                @foreach($agsNoDia as $ag)
                  @include('atendimento._card', ['ag' => $ag])
                @endforeach
              </div>
            </div>
          @endif
        @endforeach
      @endif
    </div>
  @endif

  {{-- ===== CONTEÚDO: MÊS ===== --}}
  @if($modo === 'mes')
    @php
      $porDia    = $agendamentos->groupBy('horarioProfissional.dia_semana');
      $totalMes  = 0;
    @endphp

    <div class="ag-lista-wrap">
      <div class="ag-lista-header">
        <span class="ag-lista-data">
          <i class="bi bi-calendar-month"></i>
          {{ $nomeMesFormatado }}
        </span>
      </div>

      @if($agendamentos->isEmpty())
        <div class="ag-vazio">
          <i class="bi bi-calendar-x ag-vazio-icon"></i>
          <p>Nenhum agendamento para este mês.</p>
        </div>
      @else
        <table class="custom-table" style="margin-top:8px;">
          <thead>
            <tr>
              <th>Dia da semana</th>
              <th style="text-align:center;">Alunos</th>
              <th style="text-align:center;">Semanas no mês</th>
              <th style="text-align:center;">Total de sessões</th>
            </tr>
          </thead>
          <tbody>
            @foreach([1,2,3,4,5,6,0] as $numDia)
              @if($porDia->has($numDia))
                @php
                  $qtdAlunos  = $porDia[$numDia]->count();
                  $semanas    = $ocorrenciasMes[$numDia];
                  $totalDia   = $qtdAlunos * $semanas;
                  $totalMes  += $totalDia;
                @endphp
                <tr>
                  <td>{{ $nomeDiaPorNumero[$numDia] }}</td>
                  <td style="text-align:center;">{{ $qtdAlunos }}</td>
                  <td style="text-align:center;">{{ $semanas }}×</td>
                  <td style="text-align:center; font-weight:700;">{{ $totalDia }}</td>
                </tr>
              @endif
            @endforeach
          </tbody>
          <tfoot>
            <tr style="background:#f1f5f9; font-weight:700;">
              <td colspan="3" style="text-align:right; padding-right:16px;">Total no mês</td>
              <td style="text-align:center; font-size:1.1rem; color:#1e293b;">{{ $totalMes }}</td>
            </tr>
          </tfoot>
        </table>
      @endif
    </div>
  @endif

</div>
</div>
</div>

@endsection
