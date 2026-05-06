<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('alunos')->count() > 0) {
            return;
        }

        $faker = \Faker\Factory::create('pt_BR');

        /*
        |--------------------------------------------------------------------------
        | ESCOLAS
        |--------------------------------------------------------------------------
        */
        $escolas = [];
        for ($i = 1; $i <= 6; $i++) {
            $escolas[] = [
                'nome' => 'Escola Municipal ' . $faker->lastName,
                'cidade' => 'Guanambi',
                'bairro' => $faker->citySuffix,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('escolas')->insert($escolas);
        $escolaIds = DB::table('escolas')->pluck('id')->toArray();

        /*
        |--------------------------------------------------------------------------
        | BASES FIXAS
        |--------------------------------------------------------------------------
        */
        DB::table('diagnostico')->insert([
            ['nome' => 'TEA', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'TDAH', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Dislexia', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('deficiencias')->insert([
            ['nome' => 'Auditiva', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Visual', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Intelectual', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('origens_encaminhamento')->insert([
            ['nome' => 'Escola', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Família', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Saúde', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('listas_espera')->insert([
            ['nome' => 'Psicologia', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Fonoaudiologia', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Psicopedagogia', 'ativo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
        $listasIds = DB::table('listas_espera')->pluck('id')->toArray();

        /*
        |--------------------------------------------------------------------------
        | PROFISSIONAIS
        |--------------------------------------------------------------------------
        */
        $profissionais = [];
        foreach (['Psicólogo', 'Fonoaudiólogo', 'Psicopedagogo'] as $profissao) {
            for ($i = 0; $i < 2; $i++) {
                $profissionais[] = [
                    'nome' => $faker->name,
                    'cpf' => $faker->unique()->cpf(false),
                    'profissao' => $profissao,
                    'ativo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        DB::table('profissionais')->insert($profissionais);
        $profissionaisIds = DB::table('profissionais')->pluck('id')->toArray();

        /*
        |--------------------------------------------------------------------------
        | HORÁRIOS (agenda semanal)
        |--------------------------------------------------------------------------
        */
        foreach ($profissionaisIds as $profId) {
            foreach ([1, 2, 3, 4, 5] as $dia) {
                DB::table('horarios_profissional')->insert([
                    'profissional_id' => $profId,
                    'dia_semana' => $dia,
                    'hora_inicio' => '08:00:00',
                    'duracao_minutos' => 50,
                    'ativo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        $horarios = DB::table('horarios_profissional')->get();

        /*
        |--------------------------------------------------------------------------
        | ALUNOS (100)
        |--------------------------------------------------------------------------
        */
        $alunos = [];

        for ($i = 0; $i < 100; $i++) {
            $alunos[] = [
                'nome' => $faker->name,
                'data_nascimento' => $faker->dateTimeBetween('-12 years', '-5 years')->format('Y-m-d'),
                'sexo' => $faker->randomElement(['M', 'F']),
                'cidade' => 'Guanambi',
                'bairro' => $faker->citySuffix,
                'escola_id' => $faker->randomElement($escolaIds),
                'serie' => rand(1, 5) . 'º ano',
                'turno' => $faker->randomElement(['Matutino', 'Vespertino']),
                'possui_laudo' => $faker->boolean(70),
                'origem_encaminhamento_id' => rand(1, 3),
                'ativo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('alunos')->insert($alunos);
        $alunoIds = DB::table('alunos')->pluck('id')->toArray();

        /*
        |--------------------------------------------------------------------------
        | RELAÇÕES + LISTA DE ESPERA / AGENDAMENTO
        |--------------------------------------------------------------------------
        */
        foreach ($alunoIds as $alunoId) {

            // vínculos básicos
            DB::table('aluno_deficiencia')->insert([
                'aluno_id' => $alunoId,
                'deficiencia_id' => rand(1, 3),
            ]);

            DB::table('aluno_diagnostico')->insert([
                'aluno_id' => $alunoId,
                'diagnostico_id' => rand(1, 3),
            ]);

            // decide se o aluno já conseguiu vaga
            $temAgendamento = $faker->boolean(70);

            if ($temAgendamento) {

                $qtd = rand(2, 5);

                for ($i = 0; $i < $qtd; $i++) {

                    $horario = $horarios->random();
                    $data = Carbon::now()->subDays(rand(1, 60));

                    DB::table('agendamentos')->insert([
                        'aluno_id' => $alunoId,
                        'lista_espera_id' => rand(1, 3),
                        'horarios_profissional_id' => $horario->id,
                        'data' => $data,
                        'status' => 'agendado',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $faltou = $faker->boolean(25);

                    DB::table('registros_atendimento')->insert([
                        'aluno_id' => $alunoId,
                        'profissional_id' => $horario->profissional_id,
                        'data_atendimento' => $data,
                        'faltou' => $faltou,
                        'motivo_falta' => $faltou ? 'Não compareceu' : null,
                        'observacoes' => $faker->sentence,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } else {

                // permanece aguardando
                DB::table('lista_espera_aluno')->insert([
                    'aluno_id' => $alunoId,
                    'lista_espera_id' => rand(1, 3),
                    'data_entrada' => Carbon::now()->subDays(rand(5, 120)),
                    'status' => 'aguardando',
                ]);
            }
        }
    }
}
