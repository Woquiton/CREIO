@php
  $statusClass = match($ag->status ?? 'agendado') {
    'realizado' => 'ag-status--realizado',
    'cancelado' => 'ag-status--cancelado',
    'falta'     => 'ag-status--falta',
    default     => 'ag-status--agendado',
  };
  $statusLabel = match($ag->status ?? 'agendado') {
    'realizado' => 'Realizado',
    'cancelado' => 'Cancelado',
    'falta'     => 'Falta',
    default     => 'Agendado',
  };
@endphp

<div class="ag-card">
  <div class="ag-card-hora">
    {{ \Carbon\Carbon::parse($ag->horarioProfissional->hora_inicio)->format('H:i') }}
    <span class="ag-card-duracao">{{ $ag->horarioProfissional->duracao_minutos }}min</span>
  </div>
  <div class="ag-card-info">
    <span class="ag-card-aluno">{{ $ag->aluno->nome }}</span>
    <span class="ag-card-prof">
      <i class="bi bi-person-fill"></i> {{ $ag->horarioProfissional->profissional->nome }}
    </span>
    @if($ag->observacoes)
      <span class="ag-card-obs">{{ $ag->observacoes }}</span>
    @endif
  </div>
  <div class="ag-card-right">
    <span class="ag-status {{ $statusClass }}">{{ $statusLabel }}</span>
    <div class="ag-card-acoes">
      @if(auth()->user()->temPermissao('alunos.gerenciar'))
        <a href="{{ route('alunos.show', $ag->aluno_id) }}" class="ag-icon-btn" title="Ver aluno">
          <i class="bi bi-eye"></i>
        </a>
      @endif
      @if(!$profissionalFixo && auth()->user()->temPermissao('agendamentos.gerenciar'))
        <a href="{{ route('agendamentos.edit', $ag->id) }}" class="ag-icon-btn" title="Editar agendamento">
          <i class="bi bi-pencil"></i>
        </a>
        <form action="{{ route('agendamentos.destroy', $ag->id) }}" method="POST"
              onsubmit="return confirm('Remover este agendamento?')" style="display:inline">
          @csrf
          @method('DELETE')
          <button type="submit" class="ag-icon-btn ag-icon-btn--danger" title="Remover agendamento">
            <i class="bi bi-x-lg"></i>
          </button>
        </form>
      @endif
    </div>
  </div>
</div>
