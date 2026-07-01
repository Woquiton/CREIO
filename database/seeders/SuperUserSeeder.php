<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Perfil;
use App\Models\Permissao;
use Illuminate\Support\Facades\Hash;

class SuperUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Permissões
        $permissoes = [
            ['nome' => 'usuarios.gerenciar',      'descricao' => 'Criar, editar e desativar usuários e perfis'],
            ['nome' => 'alunos.gerenciar',         'descricao' => 'Cadastrar e editar alunos'],
            ['nome' => 'profissionais.gerenciar',  'descricao' => 'Cadastrar e editar profissionais'],
            ['nome' => 'agendamentos.visualizar',  'descricao' => 'Visualizar agendamentos'],
            ['nome' => 'agendamentos.gerenciar',   'descricao' => 'Criar e editar agendamentos'],
            ['nome' => 'atendimentos.gerenciar',   'descricao' => 'Lançar e editar atendimentos'],
            ['nome' => 'listas_espera.gerenciar',  'descricao' => 'Gerenciar filas de espera'],
            ['nome' => 'escolas.gerenciar',        'descricao' => 'Cadastrar e editar escolas'],
            ['nome' => 'configuracoes.gerenciar',  'descricao' => 'Gerenciar diagnósticos, deficiências e origens'],
            ['nome' => 'relatorios.visualizar',    'descricao' => 'Visualizar relatórios de atendimento'],
        ];

        foreach ($permissoes as $p) {
            Permissao::firstOrCreate(['nome' => $p['nome']], $p);
        }

        // 2. Perfil Administrador com todas as permissões
        $perfil = Perfil::firstOrCreate(
            ['nome' => 'Administrador'],
            ['descricao' => 'Acesso total ao sistema']
        );

        $todasPermissoes = Permissao::pluck('id');
        $perfil->permissoes()->sync($todasPermissoes);

        // 3. Usuário admin
        $user = User::firstOrCreate(
            ['email' => 'paulo@gmail.com'],
            [
                'firstName' => 'Paulo',
                'lastName'  => 'José',
                'password'  => Hash::make('P@ulo123'),
                'ativo'     => true,
                'is_super'  => true,
            ]
        );
        $user->perfis()->syncWithoutDetaching([$perfil->id]);

        $userSuporte = User::firstOrCreate(
            ['email' => 'suporte@creio.com.br'], 
            [
                'firstName' => 'Equipe',
                'lastName'  => 'Suporte',
                'password'  => Hash::make('Suporte2026@'), 
                'ativo'     => true,
                'is_super'  => true, 
            ]
        );

        $userSuporte->perfis()->syncWithoutDetaching([$perfil->id]);
    }
}
