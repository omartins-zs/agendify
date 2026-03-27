# Como Executar o Agendify

Este projeto pode rodar em 2 modos:
- Local (sem Docker)
- Docker (recomendado para ambiente padrao)

## 1) Arquivo de ambiente (.env)

Use o `.env` como arquivo principal da sua maquina.

Se ainda nao existir:

```bash
cp .env.example .env
```

No PowerShell:

```powershell
Copy-Item .env.example .env
```

Quando quiser atualizar o template com base no seu `.env`:

```bash
cp .env .env.example
```

No PowerShell:

```powershell
Copy-Item .env .env.example
```

Importante:
- antes de commitar `.env.example`, remova segredos reais (tokens, senhas, APP_KEY real).
- o `.env` deve continuar fora do Git.

## 2) Executar Local (sem Docker)

### 2.1 Configuracao recomendada no .env (local)

```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

QUEUE_CONNECTION=database
CACHE_STORE=database

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
```

### 2.2 Comandos

```bash
composer install
npm install
php artisan key:generate
```

Criar banco SQLite local (se nao existir):

```bash
touch database/database.sqlite
```

No PowerShell:

```powershell
if (!(Test-Path database/database.sqlite)) { New-Item database/database.sqlite -ItemType File | Out-Null }
```

Rodar migrations e seed:

```bash
php artisan migrate --seed
```

Subir aplicacao:

```bash
composer run dev
```

Acesso local:
- App: http://127.0.0.1:8000

## 3) Executar com Docker

O `docker-compose.yml` ja esta configurado para forcar ambiente Docker em `app`, `queue` e `scheduler`:
- PostgreSQL (`postgres`)
- Redis (`redis`)
- Mailpit (`mailpit`)

### 3.1 Comandos

```bash
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app npm install
docker compose exec app npm run build
```

### 3.2 URLs

- App: http://localhost:8000
- Mailpit: http://localhost:8025

## 4) Observacoes uteis

- Se alternar entre local e Docker, revise apenas o `.env` local.
- No Docker, os servicos principais ja recebem variaveis proprias pelo `docker-compose.yml`.
- Se o container ja estava rodando antes das mudancas de ambiente, recrie:

```bash
docker compose down
docker compose up -d --build
```
