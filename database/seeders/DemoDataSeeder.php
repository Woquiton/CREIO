<?php

namespace Database\Seeders;

use App\Models\Agendamento;
use App\Models\Aluno;
use App\Models\Deficiencia;
use App\Models\Diagnostico;
use App\Models\DocumentoAluno;
use App\Models\DocumentoAtendimento;
use App\Models\DocumentoEscola;
use App\Models\DocumentosProfissionais;
use App\Models\Escola;
use App\Models\FormacoesProfissionais;
use App\Models\HorarioProfissional;
use App\Models\ListaEspera;
use App\Models\LogAtividade;
use App\Models\OrigemEncaminhamento;
use App\Models\Perfil;
use App\Models\Permissao;
use App\Models\Profissional;
use App\Models\RegistroAtendimento;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    private \Faker\Generator $faker;

    private const PDF_STUB = "%PDF-1.0\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 300 150]>>endobj\ntrailer<</Root 1 0 R/Size 4>>\n%%EOF";

    public function run(): void
    {
        $this->faker = \Faker\Factory::create('pt_BR');
        $this->faker->seed(31415);

        DB::transaction(function () {
            $perfis = $this->criarPerfis();
            [$diagnosticos, $deficiencias, $origens, $listas] = $this->criarCatalogos();
            $escolas = $this->criarEscolas();
            $profissionais = $this->criarProfissionais($listas);
            $usuarios = $this->criarUsuarios($perfis, $profissionais);
            $alunos = $this->criarAlunos($escolas, $origens, $diagnosticos, $deficiencias, $listas);
            $agendamentos = $this->criarAgendamentosEFilas($alunos, $listas);
            $this->criarAtendimentos($agendamentos);
            $this->criarLogs($usuarios);
        });
    }

    private function criarPerfis(): array
    {
        $administrador = Perfil::firstOrCreate(['nome' => 'Administrador'], ['descricao' => 'Acesso total ao sistema']);

        $recepcao = Perfil::firstOrCreate(
            ['nome' => 'Recepção'],
            ['descricao' => 'Atendimento inicial, cadastro de alunos e organização de agenda']
        );
        $recepcao->permissoes()->sync(Permissao::whereIn('nome', [
            'alunos.gerenciar', 'agendamentos.visualizar', 'agendamentos.gerenciar',
            'listas_espera.gerenciar', 'escolas.gerenciar',
        ])->pluck('id'));

        $profissionalAtendimento = Perfil::firstOrCreate(
            ['nome' => 'Profissional de Atendimento'],
            ['descricao' => 'Realiza atendimentos e lança registros de sessão']
        );
        $profissionalAtendimento->permissoes()->sync(Permissao::whereIn('nome', [
            'atendimentos.gerenciar', 'agendamentos.visualizar', 'relatorios.visualizar',
        ])->pluck('id'));

        $coordenacao = Perfil::firstOrCreate(
            ['nome' => 'Coordenação Pedagógica'],
            ['descricao' => 'Supervisiona profissionais, diagnósticos e relatórios do centro']
        );
        $coordenacao->permissoes()->sync(Permissao::whereIn('nome', [
            'profissionais.gerenciar', 'configuracoes.gerenciar', 'relatorios.visualizar',
            'alunos.gerenciar', 'listas_espera.gerenciar',
        ])->pluck('id'));

        return compact('administrador', 'recepcao', 'profissionalAtendimento', 'coordenacao');
    }

    private function criarCatalogos(): array
    {
        $diagnosticosNomes = [
            'Transtorno do Espectro Autista (TEA)',
            'TDAH - Transtorno de Atenção e Hiperatividade',
            'Síndrome de Down',
            'Deficiência Intelectual',
            'Paralisia Cerebral',
            'Síndrome de Asperger',
            'Transtorno do Processamento Sensorial',
            'Dislexia',
        ];
        $diagnosticos = collect($diagnosticosNomes)->mapWithKeys(
            fn ($nome) => [$nome => Diagnostico::firstOrCreate(['nome' => $nome])]
        );

        $deficienciasNomes = [
            'Deficiência Física', 'Deficiência Visual', 'Deficiência Auditiva',
            'Deficiência Intelectual', 'Deficiência Múltipla', 'Deficiência Psicossocial',
            'Transtorno do Espectro Autista',
        ];
        $deficiencias = collect($deficienciasNomes)->mapWithKeys(
            fn ($nome) => [$nome => Deficiencia::firstOrCreate(['nome' => $nome])]
        );

        $origensNomes = [
            'Escola Regular', 'UBS - Unidade Básica de Saúde', 'Neurologista',
            'Psiquiatra Infantil', 'CAPS Infantil', 'Conselho Tutelar', 'Busca Espontânea (Família)',
        ];
        $origens = collect($origensNomes)->mapWithKeys(
            fn ($nome) => [$nome => OrigemEncaminhamento::firstOrCreate(['nome' => $nome])]
        );

        $listasNomes = [
            'Fila de Psicologia', 'Fila de Fonoaudiologia', 'Fila de Terapia Ocupacional',
            'Fila de Psicopedagogia', 'Fila de Fisioterapia', 'Fila de Avaliação Multidisciplinar',
        ];
        $listas = collect($listasNomes)->mapWithKeys(
            fn ($nome) => [$nome => ListaEspera::firstOrCreate(['nome' => $nome], ['ativo' => true])]
        );

        return [$diagnosticos, $deficiencias, $origens, $listas];
    }

    private function criarEscolas(): \Illuminate\Support\Collection
    {
        $dados = [
            ['nome' => 'EMEF Professora Maria de Lourdes Santos', 'bairro' => 'Centro'],
            ['nome' => 'EMEF João Batista Ferreira', 'bairro' => 'Vila Nova'],
            ['nome' => 'EMEI Pequeno Príncipe', 'bairro' => 'Jardim das Flores'],
            ['nome' => 'Escola Estadual Castro Alves', 'bairro' => 'São José'],
            ['nome' => 'EMEF Cecília Meireles', 'bairro' => 'Bela Vista'],
            ['nome' => 'Escola Municipal Monteiro Lobato', 'bairro' => 'Alto da Serra'],
            ['nome' => 'EMEI Cantinho Feliz', 'bairro' => 'Parque das Águas'],
            ['nome' => 'Escola Estadual Anísio Teixeira', 'bairro' => 'Bom Retiro'],
        ];

        $escolas = collect();
        foreach ($dados as $d) {
            $escola = Escola::create([
                'nome' => $d['nome'],
                'cnpj' => $this->faker->cnpj(true),
                'endereco' => 'Rua '.$this->faker->streetName(),
                'numero' => (string) $this->faker->numberBetween(10, 999),
                'bairro' => $d['bairro'],
                'cidade' => 'Porto Novo - MA',
                'cep' => $this->faker->numerify('65###-###'),
            ]);
            $escolas->push($escola);
        }

        foreach ($escolas->random(6) as $escola) {
            DocumentoEscola::create([
                'escola_id' => $escola->id,
                'nome_original' => 'convenio_creio_'.\Illuminate\Support\Str::slug($escola->nome).'.pdf',
                'arquivo' => \Illuminate\Support\Facades\Storage::disk('public')->putFileAs(
                    'documentos/escolas', $this->pdfTempFile(), 'convenio_'.$escola->id.'.pdf'
                ),
                'tipo_mime' => 'application/pdf',
            ]);
        }

        return $escolas;
    }

    private function criarProfissionais(\Illuminate\Support\Collection $listas): \Illuminate\Support\Collection
    {
        $quadro = [
            ['profissao' => 'Psicólogo(a)', 'qtd' => 5, 'lista' => 'Fila de Psicologia', 'registro' => 'CRP'],
            ['profissao' => 'Fonoaudiólogo(a)', 'qtd' => 4, 'lista' => 'Fila de Fonoaudiologia', 'registro' => 'CRFa'],
            ['profissao' => 'Terapeuta Ocupacional', 'qtd' => 4, 'lista' => 'Fila de Terapia Ocupacional', 'registro' => 'CREFITO-TO'],
            ['profissao' => 'Psicopedagogo(a)', 'qtd' => 3, 'lista' => 'Fila de Psicopedagogia', 'registro' => 'ABPp'],
            ['profissao' => 'Fisioterapeuta', 'qtd' => 2, 'lista' => 'Fila de Fisioterapia', 'registro' => 'CREFITO-F'],
            ['profissao' => 'Assistente Social', 'qtd' => 1, 'lista' => 'Fila de Avaliação Multidisciplinar', 'registro' => 'CRESS'],
            ['profissao' => 'Neuropsicólogo(a)', 'qtd' => 1, 'lista' => 'Fila de Avaliação Multidisciplinar', 'registro' => 'CRP'],
        ];

        $formacoesPool = [
            'Psicólogo(a)' => ['Graduação em Psicologia - UFMA', 'Pós-graduação em Neuropsicologia', 'Especialização em Análise do Comportamento Aplicada (ABA)'],
            'Fonoaudiólogo(a)' => ['Graduação em Fonoaudiologia - UFPE', 'Especialização em Linguagem Infantil', 'Curso de Motricidade Orofacial'],
            'Terapeuta Ocupacional' => ['Graduação em Terapia Ocupacional - UFBA', 'Especialização em Integração Sensorial', 'Curso de Tecnologia Assistiva'],
            'Psicopedagogo(a)' => ['Graduação em Pedagogia - UFMA', 'Pós-graduação em Psicopedagogia Clínica e Institucional'],
            'Fisioterapeuta' => ['Graduação em Fisioterapia - UFC', 'Especialização em Neurofuncional Infantil'],
            'Assistente Social' => ['Graduação em Serviço Social - UFMA', 'Especialização em Políticas Públicas de Assistência Social'],
            'Neuropsicólogo(a)' => ['Graduação em Psicologia - USP', 'Mestrado em Neuropsicologia', 'Especialização em Avaliação Neuropsicológica Infantil'],
        ];

        $horas = ['08:00:00', '09:00:00', '10:00:00', '14:00:00', '15:00:00', '16:00:00'];
        $dias = [1, 2, 3, 4, 5];

        $profissionais = collect();
        $indiceGlobal = 0;

        foreach ($quadro as $grupo) {
            for ($i = 0; $i < $grupo['qtd']; $i++) {
                $genero = $this->faker->randomElement(['M', 'F']);
                $primeiroNome = $genero === 'M' ? $this->faker->firstNameMale() : $this->faker->firstNameFemale();
                $nome = $primeiroNome.' '.$this->faker->lastName().' '.$this->faker->lastName();

                $registro = match ($grupo['registro']) {
                    'CRP' => 'CRP 06/'.$this->faker->unique()->numberBetween(10000, 99999),
                    'CRFa' => 'CRFa 2-'.$this->faker->unique()->numberBetween(10000, 19999),
                    'CREFITO-TO' => 'CREFITO 5-'.$this->faker->unique()->numberBetween(10000, 19999).'-TO',
                    'CREFITO-F' => 'CREFITO 5-'.$this->faker->unique()->numberBetween(10000, 19999).'-F',
                    'ABPp' => 'ABPp '.$this->faker->unique()->numberBetween(1000, 9999),
                    'CRESS' => 'CRESS '.$this->faker->unique()->numberBetween(10000, 19999),
                    default => (string) $this->faker->unique()->numberBetween(10000, 99999),
                };

                $profissional = Profissional::create([
                    'nome' => $nome,
                    'data_nascimento' => Carbon::now()->subYears($this->faker->numberBetween(26, 58))->subDays($this->faker->numberBetween(0, 365))->toDateString(),
                    'rg' => $this->faker->rg(),
                    'cpf' => $this->faker->unique()->cpf(),
                    'celular' => $this->faker->numerify('(98) 9####-####'),
                    'numero_registro' => $registro,
                    'profissao' => $grupo['profissao'],
                    'especializacao' => $this->faker->randomElement($formacoesPool[$grupo['profissao']]),
                    'ativo' => $this->faker->boolean(90),
                ]);

                foreach ($this->faker->randomElements($formacoesPool[$grupo['profissao']], min(2, count($formacoesPool[$grupo['profissao']]))) as $descricao) {
                    FormacoesProfissionais::create([
                        'profissional_id' => $profissional->id,
                        'descricao' => $descricao,
                    ]);
                }

                DocumentosProfissionais::create([
                    'profissional_id' => $profissional->id,
                    'nome_original' => 'diploma_'.\Illuminate\Support\Str::slug($profissional->nome).'.pdf',
                    'arquivo' => \Illuminate\Support\Facades\Storage::disk('public')->putFileAs(
                        'documentos/profissionais', $this->pdfTempFile(), 'diploma_'.$profissional->id.'.pdf'
                    ),
                    'tipo_mime' => 'application/pdf',
                ]);

                $listaPrincipal = $listas[$grupo['lista']];
                $sync = [$listaPrincipal->id];
                if ($this->faker->boolean(20) && $grupo['lista'] !== 'Fila de Avaliação Multidisciplinar') {
                    $sync[] = $listas['Fila de Avaliação Multidisciplinar']->id;
                }
                $profissional->listasEspera()->sync($sync);

                $dia1 = $dias[$indiceGlobal % count($dias)];
                $dia2 = $dias[($indiceGlobal + 2) % count($dias)];
                $hora1 = $horas[$indiceGlobal % count($horas)];
                $hora2 = $horas[($indiceGlobal + 3) % count($horas)];

                HorarioProfissional::create([
                    'profissional_id' => $profissional->id,
                    'dia_semana' => $dia1,
                    'hora_inicio' => $hora1,
                    'duracao_minutos' => 50,
                    'ativo' => true,
                ]);
                HorarioProfissional::create([
                    'profissional_id' => $profissional->id,
                    'dia_semana' => $dia2,
                    'hora_inicio' => $hora2,
                    'duracao_minutos' => 50,
                    'ativo' => true,
                ]);

                $profissionais->push($profissional);
                $indiceGlobal++;
            }
        }

        return $profissionais;
    }

    private function criarUsuarios(array $perfis, \Illuminate\Support\Collection $profissionais): \Illuminate\Support\Collection
    {
        $usuarios = collect();

        foreach ($profissionais->take(10) as $profissional) {
            $partes = explode(' ', $profissional->nome);
            $firstName = $partes[0];
            $lastName = implode(' ', array_slice($partes, 1));
            $email = \Illuminate\Support\Str::slug($firstName.'.'.$partes[1] ?? 'prof').'@creio.local';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'password' => Hash::make('Profissional@123'),
                    'ativo' => true,
                    'is_super' => false,
                ]
            );
            $user->perfis()->syncWithoutDetaching([$perfis['profissionalAtendimento']->id]);
            $profissional->update(['user_id' => $user->id]);
            $usuarios->push($user);
        }

        $recepcionista = User::firstOrCreate(
            ['email' => 'recepcao@creio.local'],
            [
                'firstName' => 'Fernanda',
                'lastName' => 'Oliveira Costa',
                'password' => Hash::make('Recepcao@123'),
                'ativo' => true,
                'is_super' => false,
            ]
        );
        $recepcionista->perfis()->syncWithoutDetaching([$perfis['recepcao']->id]);
        $usuarios->push($recepcionista);

        $coordenadora = User::firstOrCreate(
            ['email' => 'coordenacao@creio.local'],
            [
                'firstName' => 'Marta',
                'lastName' => 'Ribeiro Almeida',
                'password' => Hash::make('Coordena@123'),
                'ativo' => true,
                'is_super' => false,
            ]
        );
        $coordenadora->perfis()->syncWithoutDetaching([$perfis['coordenacao']->id]);
        $usuarios->push($coordenadora);

        $existentes = User::whereIn('email', ['paulo@gmail.com', 'suporte@creio.com.br', 'admin@admin.com'])->get();
        foreach ($existentes as $u) {
            $usuarios->push($u);
        }

        return $usuarios;
    }

    private function criarAlunos(
        \Illuminate\Support\Collection $escolas,
        \Illuminate\Support\Collection $origens,
        \Illuminate\Support\Collection $diagnosticos,
        \Illuminate\Support\Collection $deficiencias,
        \Illuminate\Support\Collection $listas
    ): \Illuminate\Support\Collection {
        $templates = [
            ['diagnostico' => 'Transtorno do Espectro Autista (TEA)', 'deficiencias' => ['Transtorno do Espectro Autista'], 'listas' => ['Fila de Psicologia', 'Fila de Terapia Ocupacional', 'Fila de Fonoaudiologia'], 'grau' => 'Moderado'],
            ['diagnostico' => 'TDAH - Transtorno de Atenção e Hiperatividade', 'deficiencias' => [], 'listas' => ['Fila de Psicologia', 'Fila de Psicopedagogia'], 'grau' => 'Leve'],
            ['diagnostico' => 'Síndrome de Down', 'deficiencias' => ['Deficiência Intelectual'], 'listas' => ['Fila de Fonoaudiologia', 'Fila de Terapia Ocupacional', 'Fila de Psicopedagogia'], 'grau' => 'Moderado'],
            ['diagnostico' => 'Deficiência Intelectual', 'deficiencias' => ['Deficiência Intelectual'], 'listas' => ['Fila de Psicopedagogia', 'Fila de Terapia Ocupacional'], 'grau' => 'Moderado'],
            ['diagnostico' => 'Paralisia Cerebral', 'deficiencias' => ['Deficiência Física', 'Deficiência Múltipla'], 'listas' => ['Fila de Fisioterapia', 'Fila de Terapia Ocupacional'], 'grau' => 'Intenso'],
            ['diagnostico' => 'Síndrome de Asperger', 'deficiencias' => ['Transtorno do Espectro Autista'], 'listas' => ['Fila de Psicologia', 'Fila de Psicopedagogia'], 'grau' => 'Leve'],
            ['diagnostico' => 'Transtorno do Processamento Sensorial', 'deficiencias' => [], 'listas' => ['Fila de Terapia Ocupacional'], 'grau' => 'Leve'],
            ['diagnostico' => 'Dislexia', 'deficiencias' => [], 'listas' => ['Fila de Psicopedagogia'], 'grau' => 'Leve'],
        ];

        $series = ['Educação Infantil', '1º Ano', '2º Ano', '3º Ano', '4º Ano', '5º Ano', '6º Ano', '7º Ano', '8º Ano', '9º Ano'];
        $estadosCivis = ['Solteiro(a)', 'Casado(a)', 'Divorciado(a)', 'União Estável'];
        $medicamentos = ['Risperidona 1mg - 1x ao dia', 'Metilfenidato 10mg - pela manhã', 'Sertralina 25mg - 1x ao dia'];
        $alergiasMed = ['Dipirona', 'Amoxicilina', 'Ibuprofeno'];
        $alergiasAlim = ['Amendoim', 'Leite e derivados', 'Ovo', 'Frutos do mar'];

        $alunos = collect();

        for ($i = 0; $i < 20; $i++) {
            $genero = $this->faker->randomElement(['M', 'F']);
            $primeiroNome = $genero === 'M' ? $this->faker->firstNameMale() : $this->faker->firstNameFemale();
            $sobrenome = $this->faker->lastName().' '.$this->faker->lastName();
            $nome = $primeiroNome.' '.$sobrenome;

            $idade = $this->faker->numberBetween(4, 15);
            $nascimento = Carbon::now()->subYears($idade)->subDays($this->faker->numberBetween(0, 364));

            $template = $templates[$i % count($templates)];

            $alergicoMedicamento = $this->faker->boolean(15);
            $alergicoAlimento = $this->faker->boolean(15);
            $usaMedicacao = $this->faker->boolean(30);
            $possuiLaudo = $this->faker->boolean(80);
            $ativo = $i >= 18 ? false : true;

            $mae = $this->faker->firstNameFemale().' '.$sobrenome;
            $pai = $this->faker->firstNameMale().' '.$sobrenome;

            $aluno = Aluno::create([
                'nome' => $nome,
                'data_nascimento' => $nascimento->toDateString(),
                'sexo' => $genero,
                'celular' => $this->faker->boolean(60) ? $this->faker->numerify('(98) 9####-####') : null,

                'endereco' => 'Rua '.$this->faker->streetName(),
                'numero' => (string) $this->faker->numberBetween(1, 999),
                'bairro' => $this->faker->randomElement(['Centro', 'Vila Nova', 'Jardim das Flores', 'São José', 'Bela Vista', 'Alto da Serra', 'Parque das Águas', 'Bom Retiro']),
                'cep' => $this->faker->numerify('65###-###'),
                'cidade' => 'Porto Novo - MA',
                'tel_residencial' => $this->faker->boolean(40) ? $this->faker->numerify('(98) 3###-####') : null,

                'escola_id' => $escolas->random()->id,
                'serie' => $series[min($idade - 4, count($series) - 1)],
                'turno' => $this->faker->randomElement(['Manhã', 'Tarde']),

                'filiacao1' => $mae,
                'filiacao2' => $pai,

                'alergico_medicamento' => $alergicoMedicamento,
                'alergico_medicamento_qual' => $alergicoMedicamento ? $this->faker->randomElement($alergiasMed) : null,
                'alergico_alimento' => $alergicoAlimento,
                'alergico_alimento_qual' => $alergicoAlimento ? $this->faker->randomElement($alergiasAlim) : null,
                'usa_medicacao' => $usaMedicacao,
                'usa_medicacao_qual' => $usaMedicacao ? $this->faker->randomElement($medicamentos) : null,
                'profissionais_crianca' => $this->faker->boolean(25) ? 'Acompanhado(a) por neurologista particular' : null,

                'resp_nome' => $mae,
                'resp_data_nascimento' => $nascimento->copy()->subYears($this->faker->numberBetween(20, 35))->toDateString(),
                'resp_rg' => $this->faker->rg(),
                'resp_cpf' => $this->faker->unique()->cpf(),
                'resp_estado_civil' => $this->faker->randomElement($estadosCivis),

                'grau_suporte' => $template['grau'],
                'possui_laudo' => $possuiLaudo,
                'origem_encaminhamento_id' => $origens->random()->id,
                'data_diagnostico' => $possuiLaudo ? $nascimento->copy()->addYears($this->faker->numberBetween(2, $idade > 3 ? $idade - 1 : 2))->toDateString() : null,

                'ativo' => $ativo,
                'justificativa_desligamento' => $ativo ? null : $this->faker->randomElement([
                    'Mudança de cidade da família',
                    'Concluiu o acompanhamento e recebeu alta',
                ]),
            ]);

            $aluno->diagnosticos()->sync([$diagnosticos[$template['diagnostico']]->id]);
            if (! empty($template['deficiencias'])) {
                $aluno->deficiencias()->sync(collect($template['deficiencias'])->map(fn ($d) => $deficiencias[$d]->id));
            }

            DocumentoAluno::create([
                'aluno_id' => $aluno->id,
                'nome_original' => $possuiLaudo ? 'laudo_medico.pdf' : 'declaracao_escolar.pdf',
                'arquivo' => \Illuminate\Support\Facades\Storage::disk('public')->putFileAs(
                    'documentos/alunos', $this->pdfTempFile(), 'documento_'.$aluno->id.'.pdf'
                ),
                'tipo_mime' => 'application/pdf',
            ]);
            if ($this->faker->boolean(30)) {
                DocumentoAluno::create([
                    'aluno_id' => $aluno->id,
                    'nome_original' => 'relatorio_desenvolvimento.pdf',
                    'arquivo' => \Illuminate\Support\Facades\Storage::disk('public')->putFileAs(
                        'documentos/alunos', $this->pdfTempFile(), 'relatorio_'.$aluno->id.'.pdf'
                    ),
                    'tipo_mime' => 'application/pdf',
                ]);
            }

            $aluno->templateListas = $template['listas'];
            $alunos->push($aluno);
        }

        return $alunos;
    }

    private function criarAgendamentosEFilas(\Illuminate\Support\Collection $alunos, \Illuminate\Support\Collection $listas): \Illuminate\Support\Collection
    {
        $agendamentos = collect();
        $horariosUsados = [];
        $contadorAgendado = 0;

        foreach ($alunos as $index => $aluno) {
            if (! $aluno->ativo) {
                continue;
            }

            foreach ($aluno->templateListas as $nomeLista) {
                $lista = $listas[$nomeLista];
                $dataEntrada = Carbon::now()->subDays($this->faker->numberBetween(5, 180));

                $vaiAgendar = $contadorAgendado < 16 && $this->faker->boolean(65);

                $aluno->listasEspera()->attach($lista->id, [
                    'data_entrada' => $dataEntrada->toDateString(),
                    'status' => $vaiAgendar ? 'agendado' : 'aguardando',
                ]);

                if (! $vaiAgendar) {
                    continue;
                }

                $profissional = $lista->profissionais()->where('ativo', true)->inRandomOrder()->first();
                if (! $profissional) {
                    continue;
                }

                $horario = $profissional->horarios()
                    ->whereNotIn('id', $horariosUsados)
                    ->inRandomOrder()
                    ->first();
                if (! $horario) {
                    continue;
                }
                $horariosUsados[] = $horario->id;

                $hoje = Carbon::now();
                $proximaData = $hoje->dayOfWeek === $horario->dia_semana
                    ? $hoje->copy()
                    : $hoje->copy()->next($horario->dia_semana);

                $status = $this->faker->randomElement(['agendado', 'agendado', 'agendado', 'agendado', 'realizado', 'realizado', 'cancelado', 'falta']);

                $agendamento = Agendamento::create([
                    'aluno_id' => $aluno->id,
                    'lista_espera_id' => $lista->id,
                    'horarios_profissional_id' => $horario->id,
                    'data' => $status === 'agendado' ? $proximaData->toDateString() : $proximaData->copy()->subWeek()->toDateString(),
                    'status' => $status,
                    'observacoes' => $this->faker->boolean(30) ? 'Atendimento semanal recorrente conforme plano terapêutico.' : null,
                ]);

                $agendamento->profissionalRef = $profissional;
                $agendamento->horarioRef = $horario;
                $agendamentos->push($agendamento);
                $contadorAgendado++;
            }
        }

        return $agendamentos;
    }

    private function criarAtendimentos(\Illuminate\Support\Collection $agendamentos): void
    {
        $atividadesPorProfissao = [
            'Psicólogo(a)' => ['Sessão de regulação emocional através de jogos terapêuticos', 'Trabalho de habilidades sociais em grupo lúdico', 'Escuta terapêutica e brincar simbólico'],
            'Fonoaudiólogo(a)' => ['Exercícios de articulação de fonemas e estimulação de linguagem oral', 'Estimulação de vocabulário com apoio de figuras', 'Treino de motricidade orofacial'],
            'Terapeuta Ocupacional' => ['Atividades de coordenação motora fina com massinha e encaixe de peças', 'Estimulação de integração sensorial com circuito tátil', 'Treino de atividades de vida diária (AVDs)'],
            'Psicopedagogo(a)' => ['Atividades de leitura e escrita com apoio visual', 'Jogos de raciocínio lógico-matemático', 'Trabalho de organização e planejamento de tarefas escolares'],
            'Fisioterapeuta' => ['Exercícios de fortalecimento muscular e alongamento', 'Estimulação de equilíbrio e marcha', 'Treino de mobilidade com apoio de órteses'],
            'Assistente Social' => ['Escuta e orientação familiar sobre acesso a benefícios sociais', 'Encaminhamento à rede de proteção social'],
            'Neuropsicólogo(a)' => ['Aplicação de bateria de testes neuropsicológicos', 'Devolutiva de avaliação cognitiva à família'],
        ];

        $motivosFalta = ['Aluno apresentou febre', 'Consulta médica no mesmo horário', 'Imprevisto familiar', 'Transporte escolar não compareceu'];

        foreach ($agendamentos as $agendamento) {
            $profissional = $agendamento->profissionalRef;
            $horario = $agendamento->horarioRef;
            $atividades = $atividadesPorProfissao[$profissional->grupoProfissao] ?? ['Atendimento terapêutico individualizado'];

            $qtdSessoes = $this->faker->numberBetween(2, 4);
            $dataBase = Carbon::parse($agendamento->data);

            for ($s = 0; $s < $qtdSessoes; $s++) {
                $dataSessao = $dataBase->copy()->subWeeks($s + 1);
                if ($dataSessao->isFuture()) {
                    continue;
                }

                $faltou = $this->faker->boolean(12);

                $registro = RegistroAtendimento::create([
                    'aluno_id' => $agendamento->aluno_id,
                    'profissional_id' => $profissional->id,
                    'data_atendimento' => $dataSessao->toDateString(),
                    'atividades_planejadas' => $this->faker->randomElement($atividades),
                    'faltou' => $faltou,
                    'motivo_falta' => $faltou ? $this->faker->randomElement($motivosFalta) : null,
                    'resumo' => $faltou ? null : 'Sessão realizada conforme planejado, com boa participação e engajamento nas atividades propostas.',
                    'observacoes' => $faltou ? null : $this->faker->randomElement($atividades),
                ]);

                if (! $faltou && $this->faker->boolean(35)) {
                    DocumentoAtendimento::create([
                        'registro_atendimento_id' => $registro->id,
                        'nome_original' => 'ficha_evolucao_'.$dataSessao->format('Y_m_d').'.pdf',
                        'arquivo' => \Illuminate\Support\Facades\Storage::disk('public')->putFileAs(
                            'documentos/atendimentos', $this->pdfTempFile(), 'ficha_'.$registro->id.'.pdf'
                        ),
                        'tipo_mime' => 'application/pdf',
                    ]);
                }
            }
        }
    }

    private function criarLogs(\Illuminate\Support\Collection $usuarios): void
    {
        $acoes = [
            ['acao' => 'login', 'modulo' => 'Sistema', 'descricao' => 'Realizou login'],
            ['acao' => 'criou', 'modulo' => 'Aluno', 'descricao' => 'Cadastrou um novo aluno'],
            ['acao' => 'editou', 'modulo' => 'Aluno', 'descricao' => 'Atualizou dados cadastrais de um aluno'],
            ['acao' => 'criou', 'modulo' => 'Agendamento', 'descricao' => 'Criou um novo agendamento'],
            ['acao' => 'criou', 'modulo' => 'Atendimento', 'descricao' => 'Lançou registro de atendimento'],
            ['acao' => 'criou', 'modulo' => 'Lista de Espera', 'descricao' => 'Cadastrou aluno em lista de espera'],
            ['acao' => 'editou', 'modulo' => 'Profissional', 'descricao' => 'Atualizou cadastro de profissional'],
            ['acao' => 'criou', 'modulo' => 'Horário', 'descricao' => 'Cadastrou novo horário de atendimento'],
            ['acao' => 'excluiu', 'modulo' => 'Agendamento', 'descricao' => 'Removeu um agendamento'],
            ['acao' => 'editou', 'modulo' => 'Lista de Espera', 'descricao' => 'Atualizou a lista de espera'],
            ['acao' => 'logout', 'modulo' => 'Sistema', 'descricao' => 'Realizou logout'],
        ];

        for ($i = 0; $i < 18; $i++) {
            $usuario = $usuarios->random();
            $item = $this->faker->randomElement($acoes);

            LogAtividade::create([
                'user_id' => $usuario->id,
                'acao' => $item['acao'],
                'modulo' => $item['modulo'],
                'descricao' => $item['descricao'],
                'ip' => $this->faker->localIpv4(),
                'created_at' => Carbon::now()->subDays($this->faker->numberBetween(0, 90))->subMinutes($this->faker->numberBetween(0, 1000)),
            ]);
        }
    }

    private function pdfTempFile(): \Symfony\Component\HttpFoundation\File\UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'creio_seed_').'.pdf';
        file_put_contents($path, self::PDF_STUB);

        return new \Symfony\Component\HttpFoundation\File\UploadedFile($path, basename($path), 'application/pdf', null, true);
    }
}
