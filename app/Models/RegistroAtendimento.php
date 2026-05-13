<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroAtendimento extends Model
{
    protected $table = 'registros_atendimento';

    protected $fillable = [
        'aluno_id',
        'profissional_id',
        'data_atendimento',
        'atividades_planejadas',
        'faltou',
        'motivo_falta',
        'resumo',
        'observacoes',
    ];

    protected $casts = [
        'data_atendimento' => 'date',
        'faltou'           => 'boolean',
    ];

    public function aluno()
    {
        return $this->belongsTo(Aluno::class);
    }

    public function profissional()
    {
        return $this->belongsTo(\App\Models\Profissional::class);
    }

    public function documentos()
    {
        return $this->hasMany(DocumentoAtendimento::class);
    }
}
