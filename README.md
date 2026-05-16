# Contacts API

API REST para gerenciamento de contatos com cálculo assíncrono de score, construída com Laravel 11 seguindo os princípios de **DDD (Domain-Driven Design)**, **SOLID** e **TDD**.

---

## 🏗️ Arquitetura

O projeto segue uma **Arquitetura em Camadas** inspirada em DDD / Clean Architecture:

```
src/
├── Domain/Contact/          # Regras de negócio puras (sem framework)
│   ├── Entities/            # Contact — entidade rica com comportamento
│   ├── ValueObjects/        # Email, Phone, ContactName, Score
│   ├── Enums/               # ContactStatus (Backed Enum)
│   ├── Events/              # ContactScoreProcessed (domain event)
│   ├── Exceptions/          # Exceções de domínio
│   ├── Repositories/        # Interface ContactRepositoryInterface
│   └── Services/            # ScoreCalculatorService + Strategies (Strategy Pattern)
│
├── Application/             # Casos de uso — orquestram o domínio
│   ├── UseCases/Contact/    # Create, Update, Delete, Get, List, ProcessScore, TriggerProcessing
│   └── DTOs/                # CreateContactDTO, UpdateContactDTO
│
└── Infrastructure/          # Detalhes de implementação (Laravel)
    ├── Http/
    │   ├── Controllers/     # ContactController (thin controller)
    │   ├── Requests/        # Form Requests com validação
    │   └── Resources/       # ContactResource (API Resource)
    ├── Persistence/Eloquent/ # ContactModel + EloquentContactRepository
    ├── Queue/Jobs/          # ProcessContactScoreJob
    ├── Events/              # ContactScoreUpdated (Broadcast event)
    ├── Listeners/           # ContactScoreProcessedListener (log + broadcast)
    └── Providers/           # ContactServiceProvider (DI), EventServiceProvider
```

### Padrões aplicados

| Padrão | Onde |
|---|---|
| **Strategy** | `ScoreCalculatorService` + `EmailScoreStrategy`, `NameScoreStrategy`, `PhoneScoreStrategy` |
| **Value Object** | `Email`, `Phone`, `ContactName`, `Score` — imutáveis, com regras próprias |
| **Repository** | `ContactRepositoryInterface` / `EloquentContactRepository` |
| **Use Case / Application Service** | Uma classe por operação (`CreateContactUseCase`, etc.) |
| **Domain Event** | `ContactScoreProcessed` disparado após processamento |
| **Observer** (Model Event) | `ContactModel::booted()` normaliza o telefone no `saving` |

---

## 🚀 Setup com Docker

### 1. Clone e configure o ambiente

```bash
git clone <repo-url> contacts-api
cd contacts-api

cp .env.example .env
```

### 2. Suba os containers

```bash
docker compose up -d --build
```

Isso inicia:
- **app** — PHP-FPM 8.2
- **nginx** — proxy reverso na porta `8000`
- **db** — MySQL 8.0 na porta `3306`
- **redis** — Redis 7 na porta `6379`
- **queue** — worker `php artisan queue:work redis`
- **reverb** — WebSocket server na porta `8080`

### 3. Instale as dependências e gere a chave

```bash
# O Dockerfile usa --no-dev, então este passo instala as dependências de desenvolvimento
# (phpunit, faker, mockery) necessárias para rodar os testes
docker compose exec app composer install
docker compose exec app php artisan key:generate
```

### 4. Execute as migrations

```bash
docker compose exec app php artisan migrate
```

### 5. Verifique que tudo está rodando

```bash
curl http://localhost:8000/up
# → {"status": "ok"}
```

---

## ✅ Rodando os testes

> **Atenção:** o Step 3 do setup (`composer install`) é obrigatório antes de rodar os testes. Sem ele, o PHPUnit não estará disponível pois o Dockerfile instala apenas dependências de produção.

```bash
# Todos os testes (Unit + Feature)
docker compose exec app php artisan test

# Apenas testes unitários (sem banco)
docker compose exec app php artisan test --testsuite=Unit

# Apenas testes de integração
docker compose exec app php artisan test --testsuite=Feature

# Com cobertura
docker compose exec app php artisan test --coverage
```

Os testes de Feature usam **SQLite em memória** (configurado no `phpunit.xml`), portanto não dependem do MySQL.

---

## 📡 Endpoints da API

Base URL: `http://localhost:8000/api`

| Método | Rota | Ação |
|---|---|---|
| `POST` | `/contacts` | Criar contato |
| `GET` | `/contacts` | Listar contatos (paginado) |
| `GET` | `/contacts/{id}` | Exibir contato |
| `PUT` | `/contacts/{id}` | Atualizar contato |
| `DELETE` | `/contacts/{id}` | Excluir contato (soft delete) |
| `POST` | `/contacts/{id}/process-score` | Enfileirar cálculo de score |

### Exemplos

**Criar contato**
```bash
curl -X POST http://localhost:8000/api/contacts \
  -H "Content-Type: application/json" \
  -d '{
    "name": "João Silva",
    "email": "joao@empresa.com.br",
    "phone": "(11) 98765-4321"
  }'
```

**Listar com paginação**
```bash
curl "http://localhost:8000/api/contacts?page=1&per_page=10"
```

**Disparar processamento de score**
```bash
curl -X POST http://localhost:8000/api/contacts/1/process-score
```

---

## 🧮 Regras de Cálculo do Score

| Critério | Pontos |
|---|---|
| E-mail com domínio corporativo (não gmail/hotmail/yahoo) | +20 |
| E-mail com domínio `.br` | +10 |
| Nome completo (mais de uma palavra) | +10 |
| DDD do estado de SP (11–19) | +20 |
| DDD de outro estado | +10 |
| **Score máximo possível** | **60** |

O cálculo é feito via **Strategy Pattern** — cada critério é uma classe independente que implementa `ScoreStrategyInterface`. Para adicionar um novo critério, basta criar uma nova estratégia e registrá-la no `ContactServiceProvider`.

---

## 📺 WebSocket em tempo real (Laravel Reverb)

Após disparar `POST /api/contacts/{id}/process-score`, o job processa o score de forma assíncrona e emite o evento `score.updated` no canal `contacts.{id}`.

### Escutando com JavaScript (Pusher.js → Reverb)

```html
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
const pusher = new Pusher('contacts-key', {  // REVERB_APP_KEY
    wsHost: 'localhost',
    wsPort: 8080,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
    cluster: 'mt1',
});

const channel = pusher.subscribe('contacts.1'); // canal do contato ID=1

channel.bind('score.updated', function(data) {
    console.log('Score atualizado:', data.contact);
    // { id, name, email, score, status, processed_at }
});
</script>
```

Uma página de demonstração completa está disponível em `resources/views/websocket-demo.html`.

Abra-a diretamente no browser — sem precisar de build — após subir os containers.

---

## 📋 Logs

O listener `ContactScoreProcessedListener` grava em `storage/logs/contact.log`:

```
[2024-01-15 10:30:00] contact.INFO: Contact score processed
    {"id":1,"email":"joao@empresa.com.br","score":60,"status":"active"}
```

Para acompanhar em tempo real:
```bash
docker compose exec app tail -f storage/logs/contact.log
```

---

## 🔧 Comandos úteis

```bash
# Reiniciar o queue worker
docker compose restart queue

# Ver logs do app
docker compose logs -f app

# Entrar no container
docker compose exec app bash

# Limpar cache
docker compose exec app php artisan cache:clear

# Rodar Pint (code style)
docker compose exec app ./vendor/bin/pint
```

---

## 🧪 Estrutura dos Testes

```
tests/
├── Unit/
│   ├── Domain/
│   │   ├── ContactEntityTest.php        # Comportamento da entidade (transições de status)
│   │   ├── ContactStatusTest.php        # Lógica do Enum de status
│   │   ├── ScoreCalculatorServiceTest.php # Strategies de cálculo de score
│   │   ├── ContactNameTest.php          # Value Object ContactName
│   │   ├── ScoreTest.php                # Value Object Score
│   │   └── EmailTest.php                # Value Object Email
│   │   └── PhoneTest.php                # Value Object Phone
│   └── Application/
│       ├── CreateContactUseCaseTest.php # Use case com repositório mockado
│       └── ProcessContactScoreUseCaseTest.php
└── Feature/
    ├── ContactCrudTest.php              # Endpoints CRUD (banco real em memória)
    ├── ContactScoreProcessingTest.php   # Job + cálculo integrado
    └── ContactScoreProcessedListenerTest.php # Listener + evento de domínio
```

---

## 📦 Variáveis de ambiente principais

| Variável | Padrão | Descrição |
|---|---|---|
| `QUEUE_CONNECTION` | `redis` | Driver da fila |
| `BROADCAST_CONNECTION` | `reverb` | Driver de broadcast |
| `REVERB_APP_KEY` | `contacts-key` | Chave pública do Reverb |
| `REVERB_PORT` | `8080` | Porta do WebSocket |
| `DB_CONNECTION` | `mysql` | Driver do banco |