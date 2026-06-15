# Reab_Ly — Guia Completo para Criar o APK Mobile

## Stack do Backend (API REST)

| Tecnologia | Versão | Função |
|---|---|---|
| Laravel 12 | PHP 8.2+ | Framework Backend |
| Sanctum | 4.x | Autenticação via Bearer Token |
| PostgreSQL | 16+ | Banco de dados |
| Minio (S3-compatible) | — | Storage de vídeos de exercícios |
| UUID v4 | — | Primary keys de todas as entidades |

## Base URL da API

```
http://localhost:8000/api
```

Em produção, substituir pelo domínio real.

---

## 1. AUTENTICAÇÃO

### 1.1 Fluxo

```
Register → 204 No Content (já logado via session no backend)
Login    → 200 { "token": "1|abc123..." } (Bearer token)
Todas as requisições protegidas → Header: Authorization: Bearer <token>
Logout   → 200 { "message": "..." } (invalida o token)
```

### 1.2 Register — `POST /api/register`

Pública (sem token). Cria User + Profile (Doctor ou Patient).

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

**Regras:**
- `role`: `"doctor"` | `"patient"`
- Se `role: "doctor"` → obrigatório: `crefito`, `specialty`
- Se `role: "patient"` → obrigatório: `birth_date` (formato `Y-m-d`), opcional: `clinical_condition`
- `password` deve ser `confirmed` (enviar `password_confirmation`)
- `email` deve ser único, lowercase, máximo 255 caracteres
- `phone_number`: nullable, string, max 20

**Response:** `204 No Content`

---

### 1.3 Login — `POST /api/login`

Pública.

**Request:**
```json
{
  "email": "joao@email.com",
  "password": "senha123"
}
```

**Response 200:**
```json
{
  "token": "1|abc123def456..."
}
```

**Response 422 (credenciais inválidas):**
```json
{
  "message": "These credentials do not match our records.",
  "errors": { "email": ["..."] }
}
```

**Rate limit:** 5 tentativas por email+IP. Após bloquear, retorna 422 com mensagem "too many attempts". O throttle key é `{email}|{ip}`.

---

### 1.4 Logout — `POST /api/logout`

Protegida (`auth:sanctum`).

**Headers:** `Authorization: Bearer <token>`

**Response 200:**
```json
{
  "message": "Logout realizado com sucesso."
}
```

Invalida o token atual (deleta da tabela `personal_access_tokens`).

---

### 1.5 Usuário Logado — `GET /api/user`

Protegida (`auth:sanctum`). Retorna o usuário autenticado com suas relações.

**Headers:** `Authorization: Bearer <token>`

**Response 200:**
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

Se o role for `patient`, `doctor` vem `null` e `patient` vem preenchido.

---

### 1.6 Esqueci Senha — `POST /api/forgot-password`

Pública.

**Request:**
```json
{
  "email": "joao@email.com"
}
```

**Response 200:**
```json
{
  "message": "If that email address is in our system, we have sent a password reset link."
}
```

---

### 1.7 Resetar Senha — `POST /api/reset-password`

Pública.

**Request:**
```json
{
  "email": "joao@email.com",
  "token": "reset-token-from-email",
  "password": "nova-senha",
  "password_confirmation": "nova-senha"
}
```

**Response 200:**
```json
{
  "message": "Password reset successful."
}
```

---

### 1.8 Verificar Email — `GET /api/verify-email/{id}/{hash}`

Protegida (`auth:sanctum`, `signed`, `throttle:6,1`).

Endpoint de link assinado enviado por email. Se válido, marca `email_verified_at`.

---

### 1.9 Reenviar Verificação — `POST /api/email/verification-notification`

Protegida (`auth:sanctum`, `throttle:6,1`).

**Request:** (vazio — usa o usuário autenticado)

**Response 200:**
```json
{
  "message": "Verification link sent!"
}
```

---

## 2. ENDEREÇOS

Todas protegidas por `auth:sanctum`.

### 2.1 Listar — `GET /api/addresses`

Retorna endereços do usuário logado.

**Response 200:**
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

### 2.2 Criar — `POST /api/addresses`

**Request (mínimo):**
```json
{
  "user_id": "uuid",
  "postal_code": "01310-100",
  "number": "1000"
}
```

**Campos opcionais:** `street`, `neighborhood`, `city`, `state`, `complement`

**Regra:** Se `street`, `neighborhood`, `city` ou `state` não forem enviados, o backend busca automaticamente via API externa de CEP. Se o CEP não for encontrado, retorna **404**.

**Response 201:** `{ "id": "...", ... }`

### 2.3 Detalhe — `GET /api/addresses/{address}`

**Response 200:** `{ "id": "...", ... }`

### 2.4 Atualizar — `PUT /api/addresses/{address}`

**Response 200:** `{ "id": "...", ... }`

### 2.5 Excluir — `DELETE /api/addresses/{address}`

**Response 200:** `{ "message": "Endereço removido com sucesso." }`

---

## 3. PACIENTES

Todas protegidas por `auth:sanctum`. Apenas doctors (usuários com perfil doctor) podem acessar.

### 3.1 Listar — `GET /api/patients`

Retorna pacientes do doctor logado.

**Response 200:**
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

### 3.2 Criar — `POST /api/patients`

Cria um User + Patient vinculado ao doctor logado.

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

**Regras:**
- `birth_date`: obrigatório, formato `Y-m-d`, deve ser anterior a hoje (`before:today`)
- `phone_number`: opcional (default `(00) 00000-0000`)
- `email`: único, lowercase
- `password`: confirmed

**Response 201:** `{ "id": "uuid", ... }`

### 3.3 Detalhe — `GET /api/patients/{patient}`

Apenas o doctor dono pode ver.

**Response 200:**
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

### 3.4 Tratamentos do Paciente — `GET /api/patients/{patient}/treatments`

**Response 200:**
```json
[
  {
    "id": "uuid",
    "patient_id": "uuid",
    "doctor_id": "uuid",
    "title": "...",
    "status": "ongoing",
    "start_date": "2026-01-01",
    "end_date": "2026-06-01",
    "created_at": "...",
    "updated_at": "..."
  }
]
```

### 3.5 Atualizar — `PUT /api/patients/{patient}`

**Request:**
```json
{
  "birth_date": "1990-05-20",
  "clinical_condition": "Nova condição"
}
```

**Response 200:** `{ "id": "...", ... }`

### 3.6 Excluir — `DELETE /api/patients/{patient}`

**Response:** `204 No Content`

---

## 4. TRATAMENTOS

Protegidas por `auth:sanctum`.

### 4.1 Listar — `GET /api/treatments`

Apenas doctors. Retorna tratamentos do doctor logado.

**Response 200:**
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

### 4.2 Criar — `POST /api/treatments`

Apenas doctors.

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

**Status válidos:** `ongoing` | `completed` | `cancelled`
- `end_date` deve ser `after_or_equal:start_date`
- Todos os campos obrigatórios na criação

**Response 201:** `{ "data": { ... } }`

### 4.3 Detalhe — `GET /api/treatments/{treatment}`

Doctor dono **ou** Patient vinculado ao tratamento podem ver.

**Response 200:** `{ "data": { ... } }`

### 4.4 Atualizar — `PUT /api/treatments/{treatment}`

Apenas o doctor dono. Campos opcionais (envia só o que for alterar).

**Response 200:** `{ "data": { ... } }`

### 4.5 Excluir — `DELETE /api/treatments/{treatment}`

Apenas o doctor dono. Soft delete.

**Response:** `204 No Content`

---

## 5. ITENS DO TRATAMENTO

Protegidas por `auth:sanctum`.

### 5.1 Listar Itens de um Tratamento — `GET /api/treatments/{treatment}/items`

**Response 200:**
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

### 5.2 Criar Item — `POST /api/treatments/{treatment}/items`

Apenas o doctor dono do tratamento.

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

**Regras:**
- `treatment_id` é extraído da URL, não precisa enviar no body
- `sets`, `repetitions`, `duration_seconds`: opcionais, integer, mínimo 1
- `frequency_text`: opcional, string

**Response 201:** `{ "id": "...", "treatment_id": "...", ... }`

### 5.3 Detalhe — `GET /api/treatment-items/{treatmentItem}`

**Response 200:** `{ "id": "...", ... }`

### 5.4 Atualizar — `PUT /api/treatment-items/{treatmentItem}`

Apenas o doctor dono do tratamento. Campos opcionais.

**Response 200:** `{ "id": "...", ... }`

### 5.5 Excluir — `DELETE /api/treatment-items/{treatmentItem}`

Apenas o doctor dono.

**Response:** `204 No Content`

---

## 6. EXERCÍCIOS

Protegidas por `auth:sanctum`.

### 6.1 Listar — `GET /api/exercises`

Usuários só enxergam exercícios vinculados a **tratamentos deles** (via `treatment_items.exercise_id`).

**Response 200:**
```json
{
  "data": [
    {
      "id": "uuid",
      "title": "Agachamento",
      "description": "Agachamento com peso corporal",
      "category": "forca",
      "video_url": "https://bucket.s3.amazonaws.com/exercises/video.mp4",
      "created_at": "2026-01-01T00:00:00.000000Z"
    }
  ]
}
```

### 6.2 Criar — `POST /api/exercises`

Apenas doctors.

**Request (multipart/form-data se tiver vídeo):**
```json
{
  "title": "Agachamento",
  "description": "Descrição opcional",
  "category": "forca",
  "video_url": "https://..."
}
```

**Campos:**
- `title`: obrigatório, string, max 255
- `description`: opcional, string
- `category`: opcional, string, max 255
- `video_url`: opcional, string, max 255
- `video`: opcional, **file upload** (mimes: mp4,mov,avi,webm,mkv, max: 204800KB = 200MB)

**Upload de vídeo:**
Se enviar o campo `video` (arquivo), o backend faz upload para o S3/Minio no diretório `exercises/` e preenche `video_url` automaticamente.

**Response 201:** `{ "data": { ... } }`

### 6.3 Detalhe — `GET /api/exercises/{exercise}`

**Response 200:** `{ "data": { ... } }`
**Response 403:** se o exercício não pertence a nenhum tratamento do usuário

### 6.4 Atualizar — `PUT /api/exercises/{exercise}`

Apenas doctors. Se enviar novo `video`, o vídeo antigo é deletado do S3.

**Response 200:** `{ "data": { ... } }`

### 6.5 Excluir — `DELETE /api/exercises/{exercise}`

Apenas doctors. Remove também o arquivo de vídeo do S3.

**Response:** `204 No Content`

---

## 7. MODELOS DE DADOS (Database Schema)

### 7.1 `users`

| Coluna | Tipo | Restrições |
|--------|------|------------|
| id | UUID | PK |
| name | VARCHAR(255) | NOT NULL |
| email | VARCHAR(255) | UNIQUE, NOT NULL |
| email_verified_at | TIMESTAMP | NULLABLE |
| password | VARCHAR(255) | NOT NULL |
| phone_number | VARCHAR(255) | NOT NULL |
| role | VARCHAR(255) | NOT NULL |
| remember_token | VARCHAR(100) | NULLABLE |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

**Obs:** A migration original NÃO tem coluna `role`. O AppServiceProvider adiciona `$user->role` como atributo computed/getter — verificar se é campo real no banco ou apenas accessor. No RegisterRequest, `role` é usado para decidir qual perfil criar.

### 7.2 `doctors`

| Coluna | Tipo | Restrições |
|--------|------|------------|
| id | UUID | PK |
| crefito | VARCHAR(255) | NOT NULL |
| specialty | VARCHAR(255) | NOT NULL |
| user_id | UUID | FK → users(id) ON DELETE CASCADE |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### 7.3 `patients`

| Coluna | Tipo | Restrições |
|--------|------|------------|
| id | UUID | PK |
| user_id | UUID | FK → users(id) ON DELETE CASCADE |
| doctor_id | UUID | FK → doctors(id) ON DELETE SET NULL, NULLABLE |
| birth_date | DATE | NOT NULL |
| clinical_condition | TEXT | NULLABLE |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### 7.4 `addresses`

| Coluna | Tipo | Restrições |
|--------|------|------------|
| id | UUID | PK |
| user_id | UUID | FK → users(id) ON DELETE CASCADE |
| street | VARCHAR(255) | NOT NULL |
| number | VARCHAR(255) | NOT NULL |
| neighborhood | VARCHAR(255) | NOT NULL |
| city | VARCHAR(255) | NOT NULL |
| state | VARCHAR(255) | NOT NULL (2 chars esperado) |
| postal_code | VARCHAR(255) | NOT NULL |
| complement | TEXT | NULLABLE |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### 7.5 `treatments`

| Coluna | Tipo | Restrições |
|--------|------|------------|
| id | UUID | PK |
| patient_id | UUID | FK → patients(id) ON DELETE CASCADE |
| doctor_id | UUID | FK → doctors(id) ON DELETE CASCADE |
| title | VARCHAR(255) | NOT NULL |
| start_date | DATE | NOT NULL |
| end_date | DATE | NULLABLE |
| status | ENUM('ongoing','completed','cancelled') | DEFAULT 'ongoing' |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |
| deleted_at | TIMESTAMP | NULLABLE (soft delete) |

### 7.6 `treatment_items`

| Coluna | Tipo | Restrições |
|--------|------|------------|
| id | UUID | PK |
| treatment_id | UUID | FK → treatments(id) ON DELETE CASCADE |
| exercise_id | UUID | FK → exercises(id) ON DELETE CASCADE |
| sets | INTEGER | NULLABLE |
| repetitions | INTEGER | NULLABLE |
| duration_seconds | INTEGER | NULLABLE |
| frequency_text | TEXT | NULLABLE |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### 7.7 `exercises`

| Coluna | Tipo | Restrições |
|--------|------|------------|
| id | UUID | PK |
| title | VARCHAR(255) | NOT NULL |
| description | TEXT | NULLABLE |
| category | VARCHAR(255) | NULLABLE |
| video_url | VARCHAR(255) | NULLABLE |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

## 8. DIAGRAMA DE RELACIONAMENTOS

```
User (1:1) ── Doctor
User (1:1) ── Patient
User (1:N) ── Address

Doctor (1:N) ── Treatment
Patient (1:N) ── Treatment

Treatment (1:N) ── TreatmentItem
TreatmentItem (N:1) ── Exercise

Patient (N:1) ── Doctor (via doctor_id, nullable)
```

---

## 9. REGRAS DE NEGÓCIO (Autorização)

| Entidade | Quem cria | Quem vê | Quem altera/exclui |
|---|---|---|---|
| **User** | Register público | Ele mesmo (`GET /api/user`) | — |
| **Doctor** | Via Register (role=doctor) | — | — |
| **Patient** | Doctor dono | Doctor dono | Doctor dono |
| **Treatment** | Doctor | Doctor dono + Patient do tratamento | Doctor dono |
| **TreatmentItem** | Doctor dono do treatment | Doctor dono + Patient do tratamento | Doctor dono |
| **Exercise** | Qualquer doctor | Vinculados aos tratamentos do usuário | Qualquer doctor |
| **Address** | Usuário autenticado | Próprio usuário | Próprio usuário |

### Verificações manuais no PatientController:

```php
$doctor = $request->user()->doctor;
if (!$doctor) → 403 "Acesso negado"
if ($patient->doctor_id !== $doctor->id) → 403 "Acesso negado"
```

### Policies embutidas (autorização automática via `$this->authorize()`):

- **TreatmentPolicy**: `viewAny` → doctor; `view` → doctor dono ou patient do treatment; `create/update/delete` → doctor dono
- **TreatmentItemPolicy**: `viewAny/view` → patient ou doctor do treatment; `create/update/delete` → doctor do treatment
- **ExercisePolicy**: `viewAny` → doctor ou patient; `view` → vinculado aos tratamentos do user; `create/update/delete` → doctor

---

## 10. CÓDIGOS DE RESPOSTA HTTP

| Status | Significado |
|---|---|
| `200` | OK |
| `201` | Criado |
| `204` | Sem conteúdo (DELETE bem-sucedido) |
| `401` | Não autenticado (token ausente/inválido/expirado) |
| `403` | Acesso negado (sem permissão) |
| `404` | Recurso não encontrado |
| `409` | Email não verificado (middleware `verified`) |
| `422` | Erro de validação (campos inválidos) |
| `429` | Rate limit excedido |
| `500` | Erro interno do servidor |

**Formato do erro de validação (422):**
```json
{
  "message": "The title field is required. (and 1 more error)",
  "errors": {
    "title": ["The title field is required."],
    "status": ["The status field is required."]
  }
}
```

---

## 11. HEADERS PADRÃO

Toda requisição protegida deve conter:

```
Authorization: Bearer <token>
Accept: application/json
Content-Type: application/json
```

Para upload de vídeo em exercícios, usar `Content-Type: multipart/form-data`.

---

## 12. CONFIGURAÇÕES DE AMBIENTE (Mobile precisa saber)

### Variáveis de ambiente relevantes:

```env
APP_URL=http://localhost:8000          # URL base do backend
FRONTEND_URL=http://localhost:5173     # URL do frontend (para CORS)
CEP_API_URL=https://viacep.com.br/ws   # API de CEP externa
AWS_URL=http://localhost:9000/reably   # URL pública dos vídeos (Minio/S3)
```

### Observação sobre CEP:
O backend consulta uma API externa (configurada via `CEP_API_URL`) para auto-preenchimento de endereço. O formato esperado do retorno é:
```json
{
  "street": "Av. Paulista",
  "neighborhood": "Bela Vista",
  "city": "São Paulo",
  "state": "SP"
}
```

### Observação sobre Storage de Vídeos:
- Os vídeos são armazenados em bucket S3-compatível (Minio em dev, AWS S3 em prod)
- Pastas: `exercises/`
- O `video_url` retornado pela API é a URL pública do objeto no bucket

---

## 13. RESUMO DE TODAS AS ROTAS

### Públicas (sem token)

| Método | Rota | Descrição |
|--------|------|-----------|
| POST | `/api/register` | Cadastro de usuário + perfil |
| POST | `/api/login` | Login, retorna token |
| POST | `/api/forgot-password` | Solicitar reset de senha |
| POST | `/api/reset-password` | Executar reset de senha |

### Protegidas (auth:sanctum)

| Método | Rota | Descrição | Quem pode |
|--------|------|-----------|-----------|
| GET | `/api/user` | Dados do usuário logado | Qualquer autenticado |
| GET | `/api/addresses` | Listar endereços | Dono |
| POST | `/api/addresses` | Criar endereço | Autenticado |
| GET | `/api/addresses/{address}` | Detalhe endereço | Dono |
| PUT | `/api/addresses/{address}` | Atualizar endereço | Dono |
| DELETE | `/api/addresses/{address}` | Excluir endereço | Dono |
| GET | `/api/patients` | Listar pacientes | Doctor |
| POST | `/api/patients` | Criar paciente | Doctor |
| GET | `/api/patients/{patient}` | Detalhe paciente | Doctor dono |
| PUT | `/api/patients/{patient}` | Atualizar paciente | Doctor dono |
| DELETE | `/api/patients/{patient}` | Excluir paciente | Doctor dono |
| GET | `/api/patients/{patient}/treatments` | Tratamentos do paciente | Doctor dono |
| GET | `/api/treatments` | Listar tratamentos | Doctor |
| POST | `/api/treatments` | Criar tratamento | Doctor |
| GET | `/api/treatments/{treatment}` | Detalhe tratamento | Doctor dono ou patient |
| PUT | `/api/treatments/{treatment}` | Atualizar tratamento | Doctor dono |
| DELETE | `/api/treatments/{treatment}` | Excluir tratamento | Doctor dono |
| GET | `/api/treatments/{treatment}/items` | Listar itens | Doctor/Patient do treatment |
| POST | `/api/treatments/{treatment}/items` | Criar item | Doctor dono |
| GET | `/api/treatment-items/{treatmentItem}` | Detalhe item | Doctor/Patient do treatment |
| PUT | `/api/treatment-items/{treatmentItem}` | Atualizar item | Doctor dono |
| DELETE | `/api/treatment-items/{treatmentItem}` | Excluir item | Doctor dono |
| GET | `/api/exercises` | Listar exercícios | Doctor/Patient (vinculados) |
| POST | `/api/exercises` | Criar exercício | Doctor |
| GET | `/api/exercises/{exercise}` | Detalhe exercício | Vinculado |
| PUT | `/api/exercises/{exercise}` | Atualizar exercício | Doctor |
| DELETE | `/api/exercises/{exercise}` | Excluir exercício | Doctor |
| POST | `/api/logout` | Logout | Autenticado |
| GET | `/api/verify-email/{id}/{hash}` | Verificar email | Autenticado + Signed |
| POST | `/api/email/verification-notification` | Reenviar verificação | Autenticado |

---

## 14. NOTAS IMPORTANTES PARA O MOBILE

1. **UUIDs como strings** — Todos os IDs são UUID v4. O mobile deve tratá-los como strings.
2. **Formato de data** — `Y-m-d` (ex: `2026-01-01`) para campos de data (birth_date, start_date, end_date).
3. **Formato datetime** — ISO 8601 com timezone (ex: `2026-01-01T00:00:00.000000Z`) para created_at/updated_at.
4. **Token Storage** — O token retornado no login deve ser armazenado de forma segura (SharedPreferences/Keychain) e enviado em todas as requisições protegidas.
5. **Upload de vídeo** — O endpoint `POST /api/exercises` aceita `multipart/form-data` para upload de arquivo de vídeo (até 200MB, formatos: mp4, mov, avi, webm, mkv).
6. **Email verification** — O backend pode estar configurado para exigir email verificado. O middleware `verified` retorna 409 se não verificado.
7. **Rate limiting** — Login tem rate limit de 5 tentativas por minuto por email+IP.
8. **CORS** — Configurado para aceitar requisições de `FRONTEND_URL`. O mobile não sofre restrições CORS.
