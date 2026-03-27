<div align="center">

<img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/laravel/laravel-original.svg" height="44" alt="Laravel" />
<img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg" height="44" alt="PHP" />
<img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/react/react-original.svg" height="44" alt="React" />
<img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/typescript/typescript-original.svg" height="44" alt="TypeScript" />
<img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/postgresql/postgresql-original.svg" height="44" alt="PostgreSQL" />
<img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/redis/redis-original.svg" height="44" alt="Redis" />
<img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/docker/docker-original.svg" height="44" alt="Docker" />

<h1 align="center">Agendify</h1>
<p align="center">SaaS de agendamento inteligente para negocios locais.</p>

<p>
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12" />
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+" />
  <img src="https://img.shields.io/badge/React-18.2-61DAFB?style=for-the-badge&logo=react&logoColor=0D1117" alt="React 18.2" />
  <img src="https://img.shields.io/badge/TypeScript-5.x-3178C6?style=for-the-badge&logo=typescript&logoColor=white" alt="TypeScript 5.x" />
  <img src="https://img.shields.io/badge/PostgreSQL-16-4169E1?style=for-the-badge&logo=postgresql&logoColor=white" alt="PostgreSQL 16" />
  <img src="https://img.shields.io/badge/Redis-7-DC382D?style=for-the-badge&logo=redis&logoColor=white" alt="Redis 7" />
  <img src="https://img.shields.io/badge/Pest-Tests-7B68EE?style=for-the-badge" alt="Pest" />
</p>

</div>

## 📝 Descricao
O Agendify organiza agendas de pequenas empresas, evita conflitos de horario e automatiza comunicacoes de agendamento com fluxo rapido via link publico.

<cite>Sistema SaaS multi-tenant para cadastro de empresas, servicos, clientes e agendamentos com notificacoes e lembretes automatizados.</cite>

## 🚦 Status do Projeto
<h4 align="center"> ✅ Agendify 🚀 Em desenvolvimento ⚙️ </h4>

## 🏗️ Arquitetura do Projeto
- Tipo: 🧱 Monolito (Web Fullstack)
- Classificacao: backend Laravel e frontend React/Inertia no mesmo repositorio, compartilhando dominio, regras de negocio e deploy com servicos auxiliares (queue/scheduler).

## 🔥 Pre-requisitos
- PHP 8.2+ (Dockerfile atual usa `php:8.4-fpm-alpine`)
- Composer 2+
- Node.js 18+ e npm
- PostgreSQL 16+ (usa `btree_gist` para prevencao de sobreposicao)
- Redis 7+
- Docker + Docker Compose (opcional, recomendado para ambiente padrao)

## 🚀 Tecnologias Utilizadas
- Linguagens: PHP 8.2+, TypeScript 5.x
- Backend: Laravel 12, Inertia Laravel, Laravel Sanctum, Laravel Notifications
- Frontend: React 18.2, Inertia React 2, Vite 6, Tailwind CSS 3, React Hook Form, Zod
- UI: componentes em `resources/js/Components/ui` (padrao shadcn/ui), Lucide Icons
- Tabelas e dados: TanStack Table, Axios
- Banco e infraestrutura: PostgreSQL, Redis, Mailpit, Docker Compose
- Testes e qualidade: Pest, PHPUnit base Laravel
- Organizacao de codigo: MVC + Actions + Services + Jobs + Policies + Middleware de contexto por empresa

## 🔨 Funcionalidades
- Cadastro de empresa no registro com criacao automatica de usuario `owner`
- Multi-tenant por `company_id` com isolamento por middleware/policies
- Perfis de acesso: `owner`, `admin`, `attendant`, `viewer`
- CRUD de servicos com duracao, preco e status ativo/inativo
- CRUD de regras de disponibilidade por dia/horario/intervalo
- CRUD de clientes com historico basico (`appointments_count`, `last_visit_at`)
- Agenda interna com filtros por status e data
- Link publico por slug: `/book/{company:slug}`
- Geracao de slots disponiveis respeitando:
  - duracao do servico
  - regras ativas de horario
  - agendamentos ativos existentes
  - bloqueios de horario
- Prevencao de double booking:
  - validacao transacional com `lockForUpdate`
  - constraint SQL de exclusao em PostgreSQL (`appointments_no_overlap`)
- Fluxo de status do agendamento (`agendado`, `confirmado`, `realizado`, `faltou`, `cancelado`) com historico de transicoes
- Cancelamento publico por token + janela configuravel por empresa
- Dashboard com metricas de hoje e do mes (proximos, cancelamentos, faltas, taxa de comparecimento)
- Notificacoes por e-mail:
  - criacao de agendamento
  - cancelamento
  - lembretes 24h e 2h antes
- Scheduler + queue para despachar lembretes sem travar requisicoes
- Tema claro/escuro com persistencia em `localStorage`

## 🎯 Sobre o Projeto
Sistema desenvolvido demonstrando boas praticas de desenvolvimento, arquitetura limpa e organizacao de codigo, com foco em escalabilidade e manutencao.

## 📸 Preview do Projeto
🚧 Preview nao disponivel no projeto.

## 💻 Comandos
### Ambiente local
```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
php artisan migrate --seed
composer run dev
```

### Build de producao (frontend)
```bash
npm run build
```

### Testes
```bash
php artisan test
```

### Ambiente Docker
```bash
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app npm install
docker compose exec app npm run build
```

### Comandos de fila e scheduler
```bash
php artisan appointments:dispatch-reminders
php artisan queue:work --queue=default,notifications --tries=3
php artisan schedule:work
```

> ⚠️ Estes sao comandos basicos. Verifique no projeto arquivos como:
> README.md, COMO_EXECUTAR.md ou docs/ para instrucoes completas.

## 🧱 Estrutura do Projeto
```bash
agendify/
├── app/
│   ├── Actions/
│   ├── Console/Commands/
│   ├── Enums/
│   ├── Http/
│   ├── Jobs/
│   ├── Models/
│   ├── Notifications/
│   ├── Policies/
│   └── Services/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
├── routes/
├── tests/
├── docker/
├── docker-compose.yml
└── Dockerfile
```

## 📚 Documentacao e Collections
- Arquivos de documentacao encontrados: `README.md` e `Doc.txt`
- Pasta `docs/`: nao encontrada
- Collections Postman / Swagger: nao encontrados
- 🚧 O projeto nao possui documentacao automatizada ou collections disponiveis.

## 📝 Melhorias Futuras
- [ ] Criar interface de gerenciamento para `blocked_times`
- [ ] Expandir notificacoes para WhatsApp
- [ ] Adicionar testes E2E do fluxo publico de agendamento
- [ ] Incluir relatorios mais avancados de faturamento e no-show

## 🖋️ Dicas
- Seed inicial cria usuario demo:
  - email: `test@example.com`
  - senha: `password`
- Mailpit fica disponivel em `http://localhost:8025` quando usar Docker.

<div align="center">

Feito com ❤️ por Gabriel Martins 🚀

</div>
