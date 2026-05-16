# Reab_Ly — Documentação da API para Frontend

## Stack

| Tecnologia | Versão |
|---|---|
| Laravel 12 | Backend API |
| Sanctum | Autenticação (token Bearer) |
| PostgreSQL | Banco de dados |
| UUID | Primary keys de todas as entidades |

## Base URL

```
http://localhost:8000/api
```

## Autenticação

### Fluxo

```
[Register] → 204 No Content (já logado via session)
[Login]    → 200 { "token": "1|abc123..." } (Bearer token)
[Requests] → Header: Authorization: Bearer <token>
[Logout]   → 200 { "message": "..." } (invalida o token)
```

### Rotas Públicas (sem token)

| Método | Rota | Descrição |
|---|---|---|
| POST | `/api/register` | Cadastro |
| POST | `/api/login` | Login |
| POST | `/api/forgot-password` | Esqueci senha |
| POST | `/api/reset-password` | Resetar senha |

### Rotas Protegidas (exigem `Authorization: Bearer <token>`)

Todas as demais rotas usam middleware `auth:sanctum`.

---

## Registro `POST /api/register`

**Request:**
```json
{
  "name": "João Silva",
  "email": "joao@email.com",
  "password": "senha123",
  "password_confirmation": "senha123",
  "phone_number": "(11) 99999-9999",
  "role": "doctor",
  "crefito": "CREFITO-12345-F",
  "specialty": "Ortopedia"
}
```

**Roles disponíveis:** `"doctor"` | `"patient"`

**Campos condicionais:**

| Se `role: "doctor"` | Obrigatório | `crefito`, `specialty` |
|---|---|---|
| Se `role: "patient"` | Obrigatório | `birth_date` (formato `Y-m-d`) |
| | Opcional | `clinical_condition` |

**Response:** `204 No Content`

---

## Login `POST /api/login`

**Request:**
```json
{
  "email": "joao@email.com",
  "password": "senha123"
}
```

**Response `200`:**
```json
{
  "token": "1|abc123def456..."
}
```

**Response `422` (credenciais inválidas):**
```json
{
  "message": "These credentials do not match our records.",
  "errors": { "email": ["..."] }
}
```

> Rate limit: **5 tentativas** por email+IP. Após bloquear, retorna `422` com mensagem de "too many attempts".

---

## Logout `POST /api/logout`

**Headers:** `Authorization: Bearer <token>`

**Response `200`:**
```json
{
  "message": "Logout realizado com sucesso."
}
```

---

## Usuário Logado `GET /api/user`

**Headers:** `Authorization: Bearer <token>`

**Response `200`:**
```json
{
  "id": "uuid",
  "name": "João Silva",
  "email": "joao@email.com",
  "phone_number": "(11) 99999-9999",
  "role": "doctor",
  "email_verified_at": "2026-01-01T00:00:00.000000Z",
  "created_at": "2026-01-01T00:00:00.000000Z",
  "updated_at": "2026-01-01T00:00:00.000000Z",
  "doctor": {
    "id": "uuid",
    "crefito": "CREFITO-12345-F",
    "specialty": "Ortopedia",
    "user_id": "uuid"
  },
  "patient": null
}
```

> Se o role for `"patient"`, `doctor` virá `null` e `patient` virá preenchido.

---

## Exercícios

### Scopo por tratamento

Usuários só enxergam exercícios que estejam vinculados a **tratamentos deles** (via `treatment_items.exercise_id`).

### `GET /api/exercises` — Listar

**Response `200`:**
```json
{
  "data": [
    {
      "id": "uuid",
      "title": "Agachamento",
      "description": "Agachamento com peso corporal",
      "category": "forca",
      "video_url": "https://example.com/video.mp4",
      "created_at": "2026-01-01T00:00:00.000000Z"
    }
  ]
}
```

### `POST /api/exercises` — Criar (só doctor)

**Request:**
```json
{
  "title": "Agachamento",
  "description": "Descrição opcional",
  "category": "forca",
  "video_url": "https://..."
}
```

**Response `201`:** `{ "data": { ... } }`

### `GET /api/exercises/{exercise}` — Detalhe

**Response `200`:** `{ "data": { ... } }`  
**Response `403`:** se o exercício não pertence a nenhum tratamento do usuário

### `PUT /api/exercises/{exercise}` — Atualizar (só doctor)

Campos opcionais (envia só o que for alterar).

**Response `200`:** `{ "data": { ... } }`

### `DELETE /api/exercises/{exercise}` — Excluir (só doctor)

**Response `204 No Content`**

---

## Tratamentos

### `GET /api/treatments` — Listar (só doctor, retorna os dele)

**Response `200`:**
```json
{
  "data": [
    {
      "id": "uuid",
      "title": "Tratamento do Joelho",
      "status": "ongoing",
      "start_date": "2026-01-01",
      "end_date": "2026-06-01",
      "doctor_id": "uuid",
      "patient_id": "uuid",
      "created_at": "..."
    }
  ]
}
```

### `POST /api/treatments` — Criar (só doctor)

**Request:**
```json
{
  "patient_id": "uuid",
  "doctor_id": "uuid",
  "title": "Tratamento do Joelho",
  "start_date": "2026-01-01",
  "end_date": "2026-06-01",
  "status": "ongoing"
}
```

**Status válidos:** `"ongoing"` | `"completed"` | `"cancelled"`  
`start_date` é obrigatório, `end_date` deve ser `after_or_equal:start_date`  
**Response `201`:** `{ "data": { ... } }`

### `GET /api/treatments/{treatment}` — Detalhe

- Doctor dono **ou** patient dono do tratamento podem ver
- **Response `200`:** `{ "data": { ... } }`

### `PUT /api/treatments/{treatment}` — Atualizar (só doctor dono)

**Response `200`:** `{ "data": { ... } }`

### `DELETE /api/treatments/{treatment}` — Excluir (só doctor dono)

**Response `204 No Content`**

---

## Itens do Tratamento

### `GET /api/treatments/{treatment}/items` — Listar itens de um tratamento

**Response `200`:**
```json
[
  {
    "id": "uuid",
    "treatment_id": "uuid",
    "exercise_id": "uuid",
    "sets": 3,
    "repetitions": 12,
    "duration_seconds": null,
    "frequency_text": "3x por semana"
  }
]
```

### `POST /api/treatments/{treatment}/items` — Criar item (só doctor dono)

**Request:**
```json
{
  "exercise_id": "uuid",
  "sets": 3,
  "repetitions": 12,
  "duration_seconds": null,
  "frequency_text": "3x por semana"
}
```

> `treatment_id` é pego da URL, não precisa enviar. `sets` / `repetitions` / `duration_seconds` são opcionais (mínimo 1).  
> **Response `201`:** `{ "id": "...", ... }`

### `GET /api/treatment-items/{treatmentItem}` — Detalhe

**Response `200`:** `{ "id": "...", ... }`

### `PUT /api/treatment-items/{treatmentItem}` — Atualizar (só doctor dono)

**Response `200`:** `{ "id": "...", ... }`

### `DELETE /api/treatment-items/{treatmentItem}` — Excluir (só doctor dono)

**Response `204 No Content`**

---

## Pacientes

### `GET /api/patients` — Listar pacientes do doctor logado

**Response `200`:**
```json
[
  {
    "id": "uuid",
    "user_id": "uuid",
    "doctor_id": "uuid",
    "birth_date": "1990-05-20",
    "clinical_condition": "Lombalgia",
    "email": "paciente@email.com",
    "phone_number": "(11) 99999-9999"
  }
]
```

> Os campos `email` e `phone_number` vêm do `User` relacionado.

### `POST /api/patients` — Criar paciente (só doctor)

Cria um `User` + `Patient` vinculado ao doctor logado.

**Request:**
```json
{
  "name": "Maria Souza",
  "email": "maria@email.com",
  "password": "senha123",
  "password_confirmation": "senha123",
  "phone_number": "(11) 99999-9999",
  "birth_date": "1990-05-20",
  "clinical_condition": "Lombalgia"
}
```

> `birth_date` deve ser `before:today`. `phone_number` opcional (default `(00) 00000-0000`).  
> **Response `201`:** `{ "id": "...", ... }`

### `GET /api/patients/{patient}` — Detalhe (só doctor dono)

**Response `200`:**
```json
{
  "id": "uuid",
  "user_id": "uuid",
  "doctor_id": "uuid",
  "birth_date": "1990-05-20",
  "clinical_condition": "Lombalgia",
  "email": "maria@email.com",
  "phone_number": "(11) 99999-9999",
  "user": { ... },
  "treatments": [ ... ]
}
```

### `GET /api/patients/{patient}/treatments` — Tratamentos do paciente (só doctor dono)

**Response `200`:**
```json
[
  { "id": "uuid", "title": "...", "status": "ongoing", ... }
]
```

### `PUT /api/patients/{patient}` — Atualizar (só doctor dono)

**Request:**
```json
{
  "birth_date": "1990-05-20",
  "clinical_condition": "Nova condição"
}
```

**Response `200`:** `{ "id": "...", ... }`

### `DELETE /api/patients/{patient}` — Excluir (só doctor dono)

**Response `204 No Content`**

---

## Endereços

### `GET /api/addresses` — Listar endereços do usuário logado

**Response `200`:**
```json
[
  {
    "id": "uuid",
    "user_id": "uuid",
    "postal_code": "01310-100",
    "street": "Av. Paulista",
    "number": "1000",
    "neighborhood": "Bela Vista",
    "city": "São Paulo",
    "state": "SP",
    "complement": "Apto 42",
    "created_at": "...",
    "updated_at": "..."
  }
]
```

### `POST /api/addresses` — Criar

**Request (mínimo):**
```json
{
  "user_id": "uuid",
  "postal_code": "01310-100",
  "number": "1000"
}
```

> Se `street`, `neighborhood`, `city` ou `state` não forem enviados, o backend busca automaticamente via API de CEP. Se o CEP não for encontrado, retorna **404**.

**Response `201`:** `{ "id": "...", ... }`

### `GET /api/addresses/{address}` — Detalhe

**Response `200`:** `{ "id": "...", ... }`

### `PUT /api/addresses/{address}` — Atualizar

**Response `200`:** `{ "id": "...", ... }`

### `DELETE /api/addresses/{address}` — Excluir

**Response `200`:** `{ "message": "Endereco removido com sucesso." }`

---

## Regras de Negócio (Resumo)

| Entidade | Quem cria | Quem vê | Quem altera/exclui |
|---|---|---|---|
| **User** | Register público | Ele mesmo (`GET /api/user`) | — |
| **Doctor** | Via Register (role=doctor) | — | — |
| **Patient** | Doctor dono | Doctor dono | Doctor dono |
| **Treatment** | Doctor | Doctor dono + Patient do tratamento | Doctor dono |
| **TreatmentItem** | Doctor dono do treatment | Doctor dono + Patient do tratamento | Doctor dono |
| **Exercise** | Doctor | Vinculados aos tratamentos do usuário | Doctor |
| **Address** | Usuário autenticado | Próprio usuário | Próprio usuário |

## Tratamento de Erros

| Status | Significado |
|---|---|
| `200` | OK |
| `201` | Criado |
| `204` | Sem conteúdo (DELETE bem-sucedido) |
| `401` | Não autenticado (token ausente/inválido) |
| `403` | Acesso negado (sem permissão) |
| `404` | Recurso não encontrado |
| `422` | Erro de validação (campos inválidos) |
| `500` | Erro interno do servidor |

**Response de erro de validação (`422`):**
```json
{
  "message": "The title field is required. (and 1 more error)",
  "errors": {
    "title": ["The title field is required."],
    "status": ["The status field is required."]
  }
}
```

## Diagrama de Entidades

```
User (1:1) ── Doctor
User (1:1) ── Patient
User (1:N) ── Address

Doctor (1:N) ── Treatment
Patient (1:N) ── Treatment

Treatment (1:N) ── TreatmentItem
TreatmentItem (N:1) ── Exercise

Patient (N:1) ── Doctor (doctor_id opcional em Patient)
```
