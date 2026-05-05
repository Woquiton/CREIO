<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Agendamentos</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 32px; color: #111; }
    h1   { font-size: 20px; margin-bottom: 4px; }
    p.subtitulo { font-size: 13px; color: #555; margin-bottom: 24px; }

    table { width: 100%; border-collapse: collapse; font-size: 14px; }
    th { background: #1a1a2e; color: #fff; padding: 10px 12px; text-align: left; }
    td { padding: 9px 12px; border-bottom: 1px solid #ddd; }
    td.centro { text-align: center; }
    tr:nth-child(even) td { background: #f5f5f5; }

    tfoot td { background: #e8e8e8; font-weight: 700; }

    .secao-titulo {
      font-size: 15px;
      font-weight: 700;
      margin: 24px 0 8px;
      padding-bottom: 4px;
      border-bottom: 2px solid #1a1a2e;
    }

    .total-dia {
      font-size: 13px;
      color: #555;
      margin-bottom: 12px;
    }

    .btn-imprimir {
      display: inline-block;
      margin-bottom: 24px;
      padding: 8px 18px;
      background: #1a1a2e;
      color: #fff;
      border: none;
      border-radius: 6px;
      font-size: 14px;
      cursor: pointer;
    }

    @media print { .btn-imprimir { display: none; } }
  </style>
</head>
<body>

@php
  $meses = [
    1=>'janeiro', 2=>'fevereiro', 3=>'março', 4=>'abril',
    5=>'maio', 6=>'junho', 7=>'julho', 8=>'agosto',
    9=>'setembro', 10=>'outubro', 11=>'novembro', 12=>'dezembro',
  ];

  $nomeDiaPorNumero = [
    0=>'Domingo', 1=>'Segunda-feira', 2=>'Terça-feira',
    3=>'Quarta-feira', 4=>'Quinta-feira', 5=>'Sexta-feira', 6=>'Sábado',
  ];

  $inicioSemana = $dataCarbon->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
  $fimSemana    = $inicioSemana->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);

  $tituloPeriodo = match($modo) {
    'semana' => $inicioSemana->format('d/m') . ' a ' . $fimSemana->format('d/m/Y'),
    'mes'    => ucfirst($meses[(int)$dataCarbon->month]) . ' de ' . $dataCarbon->year,
    default  => $nomeDiaPorNumero[$dataCarbon->dayOfWeek] . ', '
                . $dataCarbon->day . ' de '
                . $meses[(int)$dataCarbon->month] . ' de '
                . $dataCarbon->year,
  };
@endphp

<button class="btn-imprimir" onclick="window.print()">🖨 Imprimir / Salvar PDF</button>

<h1>Relatório de Agendamentos</h1>
<p class="subtitulo">
  {{ $profissional ? 'Profissional: ' . $profissional->nome . ' — ' : '' }}{{ $tituloPeriodo }}
</p>

{{-- DIA --}}
@if($modo === 'dia')
  @if($agendamentos->isEmpty())
    <p>Nenhum agendamento para este dia.</p>
  @else
    <table>
      <thead>
        <tr>
          <th>Horário</th>
          <th>Aluno</th>
          @if(!$profissional)<th>Profissional</th>@endif
        </tr>
      </thead>
      <tbody>
        @foreach($agendamentos as $ag)
          <tr>
            <td>{{ \Carbon\Carbon::parse($ag->horarioProfissional->hora_inicio)->format('H:i') }}</td>
            <td>{{ $ag->aluno->nome }}</td>
            @if(!$profissional)<td>{{ $ag->horarioProfissional->profissional->nome }}</td>@endif
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <td colspan="{{ $profissional ? 1 : 2 }}" style="text-align:right;">Total</td>
          <td>{{ $agendamentos->count() }} agendamento{{ $agendamentos->count() !== 1 ? 's' : '' }}</td>
        </tr>
      </tfoot>
    </table>
  @endif
@endif

{{-- SEMANA --}}
@if($modo === 'semana')
  @php $porDia = $agendamentos->groupBy('horarioProfissional.dia_semana'); @endphp

  @if($agendamentos->isEmpty())
    <p>Nenhum agendamento para esta semana.</p>
  @else
    @foreach([1,2,3,4,5,6,0] as $numDia)
      @if($porDia->has($numDia))
        @php $agsNoDia = $porDia[$numDia]; @endphp
        <div class="secao-titulo">{{ $nomeDiaPorNumero[$numDia] }}</div>
        <p class="total-dia">{{ $agsNoDia->count() }} aluno{{ $agsNoDia->count() !== 1 ? 's' : '' }}</p>
        <table>
          <thead>
            <tr>
              <th>Horário</th>
              <th>Aluno</th>
              @if(!$profissional)<th>Profissional</th>@endif
            </tr>
          </thead>
          <tbody>
            @foreach($agsNoDia as $ag)
              <tr>
                <td>{{ \Carbon\Carbon::parse($ag->horarioProfissional->hora_inicio)->format('H:i') }}</td>
                <td>{{ $ag->aluno->nome }}</td>
                @if(!$profissional)<td>{{ $ag->horarioProfissional->profissional->nome }}</td>@endif
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    @endforeach

    <p style="margin-top:20px; font-weight:700; font-size:15px;">
      Total na semana: {{ $agendamentos->count() }} agendamento{{ $agendamentos->count() !== 1 ? 's' : '' }}
    </p>
  @endif
@endif

{{-- MÊS --}}
@if($modo === 'mes')
  @php
    $porDia   = $agendamentos->groupBy('horarioProfissional.dia_semana');
    $totalMes = 0;
  @endphp

  @if($agendamentos->isEmpty())
    <p>Nenhum agendamento para este mês.</p>
  @else
    <table>
      <thead>
        <tr>
          <th>Dia da semana</th>
          <th class="centro">Alunos</th>
          <th class="centro">Semanas no mês</th>
          <th class="centro">Total de sessões</th>
        </tr>
      </thead>
      <tbody>
        @foreach([1,2,3,4,5,6,0] as $numDia)
          @if($porDia->has($numDia))
            @php
              $qtdAlunos = $porDia[$numDia]->count();
              $semanas   = $ocorrenciasMes[$numDia];
              $totalDia  = $qtdAlunos * $semanas;
              $totalMes += $totalDia;
            @endphp
            <tr>
              <td>{{ $nomeDiaPorNumero[$numDia] }}</td>
              <td class="centro">{{ $qtdAlunos }}</td>
              <td class="centro">{{ $semanas }}×</td>
              <td class="centro">{{ $totalDia }}</td>
            </tr>
          @endif
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <td colspan="3" style="text-align:right; padding-right:16px;">Total no mês</td>
          <td class="centro">{{ $totalMes }}</td>
        </tr>
      </tfoot>
    </table>
  @endif
@endif

</body>
</html>
