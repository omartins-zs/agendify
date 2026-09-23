# 🔐 Acessos e Dados de Teste

Utilize as credenciais abaixo para testar as diferentes visões e permissões do sistema. Todos os usuários e registros foram gerados automaticamente via *Seeders*.

## 1. Acesso ao Sistema (Usuários de Teste)

| Perfil | E-mail / Usuário | Senha | Permissão / Detalhes |
| --- | --- | --- | --- |
| Proprietário (Owner) | `test@example.com` | `password` | Acesso total à administração da empresa "Agendify Demo", gestão de serviços, clientes e agendamentos. |

## 2. URLs Principais

| Ambiente | Aplicação (Home) | Login / Painel |
| --- | --- | --- |
| **Docker** | `http://localhost:8080` | `http://localhost:8080/login` |
| **Local** (`php artisan serve`) | `http://127.0.0.1:8000` | `http://127.0.0.1:8000/login` |

## 3. Vitrine Pública / Páginas para Clientes

| Item | Link (Exemplo Docker) |
| --- | --- |
| Landing page / Tela inicial | `http://localhost:8080/` |

## 4. Validação do Acesso

Validação da saúde da aplicação no ambiente de desenvolvimento:

| Verificação | Resultado Esperado |
| --- | --- |
| Containers (ex: `mysql`, `app`, `nginx`) | Saudáveis / Rodando |
| Tela de login principal | HTTP `200` |
| Login com usuário de teste gerado pelo seeder | Redirecionamento para Dashboard/Painel |

## 5. Carregar Dados de Teste

Caso o banco de dados seja apagado ou precise ser resetado, basta rodar os comandos abaixo para recriar todas essas credenciais e os registros iniciais da plataforma.

**Com Docker:**
```bash
docker compose exec app php artisan migrate:fresh --seed
```

**Rodando Localmente (Sem Docker):**
```bash
php artisan migrate:fresh --seed
```

---

### 📝 Observações:
- O banco de dados geralmente é alimentado com registros retroativos e informações simuladas vinculadas a esses usuários para facilitar a visualização, filtragem na tela de relatórios e validação dos fluxos.
- Use estas credenciais **apenas** em ambiente local ou Docker de desenvolvimento.
