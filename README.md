# Proposal Management API

API REST para gerenciamento de propostas comerciais — desafio técnico TsCorp.

**Stack:** Laravel 12 · PHP 8.3 · MySQL 8 · Nginx · Docker

## Requisitos

- Docker
- Docker Compose

## Início rápido

```bash
git clone <repository-url>
cd challengeTsCorp
docker compose up -d --build
```

A aplicação estará disponível em: http://localhost:8080

### Health check

```bash
curl http://localhost:8080/health
```

Resposta esperada:

```json
{
  "data": {
    "status": "ok",
    "database": "connected"
  }
}
```

## Variáveis de ambiente

Copie `.env.example` para `.env` (feito automaticamente no primeiro start do container).

| Variável | Descrição | Padrão |
|---|---|---|
| `APP_PORT` | Porta HTTP exposta pelo Nginx | `8080` |
| `DB_HOST` | Host do MySQL (Docker) | `mysql` |
| `DB_DATABASE` | Nome do banco | `proposal_api` |
| `DB_USERNAME` | Usuário do banco | `proposal` |
| `DB_PASSWORD` | Senha do banco | `secret` |
| `DB_ROOT_PASSWORD` | Senha root do MySQL | `rootsecret` |

## Comandos úteis

```bash
# Subir containers
docker compose up -d

# Parar containers
docker compose down

# Logs
docker compose logs -f

# Artisan
docker compose exec app php artisan <command>

# Composer
docker compose exec app composer <command>
```

## Estrutura Docker

```
docker/
├── nginx/default.conf   # Configuração Nginx
└── php/entrypoint.sh    # Inicialização (env + APP_KEY)
```

## Status do projeto

| Sprint | Status |
|---|---|
| 01 — Bootstrap (Docker + Laravel) | Concluída |
| 02 — Banco de dados | Pendente |

## Desvios do desafio original

O PDF do desafio utiliza rotas e campos em português. Esta implementação utiliza **inglês** por convenção corporativa. Mapeamento completo será documentado na Sprint 13.

| PDF | Implementação |
|---|---|
| `/api/v1/clientes` | `/api/v1/clients` |
| `/api/v1/propostas` | `/api/v1/proposals` |
| `/api/v1/propostas/{id}/auditoria` | `/api/v1/proposals/{id}/audit` |

## Licença

Projeto de desafio técnico.
