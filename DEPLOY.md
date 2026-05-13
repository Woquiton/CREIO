# Guia de Instalação em Produção — Sistema CREIO

Siga os passos abaixo para instalar o sistema no servidor. Em caso de dúvidas, entre em contato com o responsável pelo projeto.

## Pré-requisitos

- [Docker](https://www.docker.com/) instalado
- [Git](https://git-scm.com/) instalado
- Acesso à internet para baixar as imagens Docker

---

## Passo a passo

### 1. Clone o repositório

```bash
git clone https://github.com/Paulojcle/SistemaCREIO.git
cd SistemaCREIO
```

### 2. Configure o ambiente

```bash
cp .env.example .env
```

Abra o arquivo `.env` e preencha os campos abaixo:

```env
APP_NAME="Sistema CREIO"
APP_ENV=production
APP_KEY=                        # será preenchido no passo 3
APP_DEBUG=false
APP_URL=http://ip-do-servidor   # ex: http://192.168.1.100 ou https://seudominio.com.br

DB_CONNECTION=mysql
DB_HOST=creio_mysql             # não alterar — é o nome interno do container
DB_PORT=3306
DB_DATABASE=nome_do_banco
DB_USERNAME=usuario
DB_PASSWORD=senha_segura

FORCE_HTTPS=false               # mudar para true se o servidor tiver HTTPS configurado
```

Use uma senha forte no campo `DB_PASSWORD`. 

### 3. Gere a chave da aplicação

Esse passo cria uma chave de segurança usada pelo sistema para proteger dados e sessões. Execute:

```bash
docker compose -f docker-compose.prod.yml run --rm app php artisan key:generate --show
```

O terminal vai exibir um valor parecido com `base64:xYz123...`. Copie esse valor e cole no campo `APP_KEY` do arquivo `.env`.

### 4. Suba os containers

```bash
docker compose -f docker-compose.prod.yml up -d
```

Na primeira execução, o sistema vai criar o banco de dados e preparar tudo automaticamente. 


## Comandos úteis

```bash
# Ver status dos containers
docker compose -f docker-compose.prod.yml ps

# Ver logs em tempo real
docker compose -f docker-compose.prod.yml logs -f

# Reiniciar os containers
docker compose -f docker-compose.prod.yml restart

# Derrubar os containers (os dados do banco são preservados)
docker compose -f docker-compose.prod.yml down
```

## Atualização do sistema

Para atualizar o sistema após uma nova versão:

```bash
git pull origin main
docker compose -f docker-compose.prod.yml up -d --build
```

As migrations novas são executadas automaticamente ao reiniciar.
