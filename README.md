<h1 align="center">Sistema CREIO</h1>

<p align="center">
  Sistema web de gestão para centros de atendimento educacional especializado, desenvolvido como Trabalho de Conclusão de Curso.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11-red?logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2-blue?logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-8.0-orange?logo=mysql" alt="MySQL">
  <img src="https://img.shields.io/badge/Docker-ready-blue?logo=docker" alt="Docker">
</p>

---

## Sobre o projeto

O **Sistema CREIO** é uma plataforma de gestão desenvolvida para organizar e facilitar o trabalho do Centro de Referência da Educação Inclusiva (CREIO). O sistema centraliza o controle de alunos, profissionais, agendamentos, registros de atendimento, entre outros.

## Módulos

| Módulo | Descrição |
|---|---|
| **Alunos** | Cadastro, edição e visualização de alunos atendidos |
| **Profissionais** | Gestão dos profissionais e suas formações |
| **Horários** | Controle de disponibilidade de horários por profissional |
| **Agendamentos** | Agendamento de sessões entre aluno e profissional |
| **Registro de Atendimento** | Lançamento e histórico dos atendimentos realizados |
| **Lista de Espera** | Gerenciamento da fila de espera de alunos |
| **Escolas** | Cadastro das escolas de origem dos alunos |
| **Relatórios** | Geração de relatórios de atendimentos |
| **Usuários e Perfis** | Controle de acesso com permissões por perfil |
| **Logs de Atividade** | Rastreamento de ações realizadas no sistema |

## Tecnologias

- **Backend:** Laravel 11 + PHP 8.2
- **Banco de dados:** MySQL 8.0
- **Frontend:** Blade + CSS + HTML + Bootstrap
- **Servidor web:** Nginx
- **Containerização:** Docker + Docker Compose

## Pré-requisitos

- [Docker](https://www.docker.com/) instalado
- [Git](https://git-scm.com/) instalado

## Como rodar o projeto

### 1. Clone o repositório

```bash
git clone https://github.com/Paulojcle/SistemaCREIO.git
cd SistemaCREIO
```

### 2. Configure o ambiente

Copie o arquivo de exemplo e ajuste as variáveis:

```bash
cp .env.example .env
```

Variáveis principais a configurar no `.env`:

```env
APP_NAME="Sistema CREIO"
APP_ENV=local
APP_URL=http://localhost:8082

DB_DATABASE=seu_banco_de_dados
DB_USERNAME=seu_nome
DB_PASSWORD=sua_senha
```

### 3. Suba os containers

```bash
docker compose -f docker-compose.dev.yml up -d
```
ou 

```bash
docker compose -f docker-compose.prod.yml up -d
```

### 4. Execute as migrations

```bash
docker compose -f docker-compose.dev.yml exec creio_php php artisan migrate --seed
```

### 5. Acesse o sistema

Abra o navegador em: [http://localhost:8082](http://localhost:8082)

---

## Comandos úteis

```bash
# Ver status dos containers
docker compose -f docker-compose.dev.yml ps

# Ver logs em tempo real
docker compose -f docker-compose.dev.yml logs -f

# Limpar cache do Laravel
docker compose -f docker-compose.dev.yml exec creio_php php artisan cache:clear
docker compose -f docker-compose.dev.yml exec creio_php php artisan config:clear

# Derrubar os containers
docker compose -f docker-compose.dev.yml down
```

## Variáveis de ambiente relevantes

| Variável | Descrição | Padrão |
|---|---|---|
| `APP_ENV` | Ambiente da aplicação (`local` ou `production`) | `local` |
| `APP_URL` | URL base da aplicação | `http://localhost:8082` |
| `FORCE_HTTPS` | Forçar HTTPS nas URLs geradas | `false` |
| `DB_DATABASE` | Nome do banco de dados | — |
| `DB_USERNAME` | Usuário do banco | — |
| `DB_PASSWORD` | Senha do banco | — |

## Licença

Este projeto foi desenvolvido para fins acadêmicos como Trabalho de Conclusão de Curso.
