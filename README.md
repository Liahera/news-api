# News API (Laravel 12)

Simple versioned REST API for user authentication and news management with public and private parts.

- Laravel 12 (API starter kit)
- Sanctum token-based authentication
- Versioned API: `/api/v1/...`
- SQLite (by default, can be switched to any DB)
- News with content blocks and publication status
- Basic search & filtering for all index endpoints

---

## Requirements

- PHP 8.2+
- Composer
- Node.js & npm (only if you need frontend, for API it’s optional)
- Laravel Herd / Valet / built-in `php artisan serve`
- SQLite (default) or any supported DB

---

## Installation

1. Clone the repository:

   ```bash
   git clone <repo-url> news-api
   cd news-api
   ```

2. Install dependencies:

   ```bash
   composer install
   ```

3. Copy environment file:

   ```bash
   cp .env.example .env
   ```

4. Configure database (SQLite by default):

   In `.env`:

   ```env
   DB_CONNECTION=sqlite
   ```

   Create DB file:

   ```bash
   mkdir -p database
   touch database/database.sqlite
   ```

5. Generate app key:

   ```bash
   php artisan key:generate
   ```

6. Run migrations:

   ```bash
   php artisan migrate
   ```

7. (Optional) Seed demo data:

   ```bash
   php artisan db:seed
   ```

8. Run the server:

   ```bash
   php artisan serve
   ```

   API will be available, for example, at:

   ```text
   http://news-api.test  (Herd)
   or
   http://127.0.0.1:8000
   ```

---

## Authentication

Authentication is done via **Laravel Sanctum** (Bearer token).

### Register

**POST** `/api/v1/auth/register`

**Body (JSON):**

```json
{
  "name": "Test User",
  "email": "test@example.com",
  "password": "secret123",
  "password_confirmation": "secret123"
}
```

**Response (201):**

```json
{
  "user": {
    "id": 1,
    "name": "Test User",
    "email": "test@example.com"
  },
  "token": "plain-text-token-here"
}
```

### Login

**POST** `/api/v1/auth/login`

```json
{
  "email": "test@example.com",
  "password": "secret123"
}
```

**Response:**

```json
{
  "user": { },
  "token": "plain-text-token-here"
}
```

Use this token in all protected requests:

```http
Authorization: Bearer <token>
```

### Get current user

**GET** `/api/v1/auth/profile`

Headers:

```http
Authorization: Bearer <token>
Accept: application/json
```

### Update profile

**PUT** `/api/v1/auth/profile`

```json
{
  "name": "New Name",
  "email": "new-email@example.com"
}
```

### Logout

**POST** `/api/v1/auth/logout`

Revokes the current access token.

---

## News Domain

### News structure

`news` table:

- `id`
- `author_id` (FK → users)
- `title` — title of the news
- `slug` — auto-generated slug based on title
- `image_path` — main image path (nullable)
- `short_description` — short summary (nullable)
- `is_published` — `true/false` (visibility)
- `published_at` — publication datetime (nullable)
- timestamps

### News blocks structure

`news_blocks` table:

- `id`
- `news_id` (FK → news)
- `type` — one of:
  - `text`
  - `image`
  - `text_image_left`
  - `text_image_right`
- `text` — nullable text content
- `image_path` — nullable image path (reserved for future)
- `position` — integer, used for ordering
- timestamps

---

## Private News API (authenticated user’s news)

All routes require:

```http
Authorization: Bearer <token>
```

Prefix: `/api/v1/news`

### List my news

**GET** `/api/v1/news/my`

Supports basic search & filtering:

Query params:

- `q` — search in `title` and `short_description`
- `status` — `true` or `false` (filter by `is_published`)
- `from` — `YYYY-MM-DD` (filter by `created_at >= from`)
- `to` — `YYYY-MM-DD` (filter by `created_at <= to`)

**Example:**

```text
GET /api/v1/news/my?q=laravel&status=true&from=2025-01-01&to=2025-12-31
```

Response: standard Laravel paginator JSON (`data`, `meta`, `links`).

---

### Create news

**POST** `/api/v1/news`

Content type can be:
- `application/json` (without file upload)
- `multipart/form-data` (if sending image upload)

**JSON example:**

```json
{
  "title": "My first news",
  "short_description": "Short description for the news",
  "is_published": true,
  "blocks": [
    {
      "type": "text",
      "text": "Main text block",
      "position": 1
    },
    {
      "type": "text_image_right",
      "text": "Text with image on the right",
      "position": 2
    }
  ]
}
```

If `is_published = true` and `published_at` is not provided, it will be set to `now()`.

---

### Show single own news

**GET** `/api/v1/news/{news}`

- `{news}` — ID of the news.
- Only the **author** can access this endpoint (policy-based access control).

---

### Update news

**PUT** `/api/v1/news/{news}`

Same structure as `POST`, but all fields are optional (validated with `sometimes` in `UpdateNewsRequest`):

```json
{
  "title": "Updated title",
  "short_description": "Updated short description",
  "is_published": true,
  "blocks": [
    {
      "type": "text",
      "text": "Updated first block",
      "position": 1
    }
  ]
}
```

Blocks strategy (simple):

- On update, if `blocks` are present, **all existing blocks are deleted** and re-created based on the request.

---

### Change publication status (hide/show)

**PATCH** `/api/v1/news/{news}/status`

```json
{
  "is_published": true
}
```

Behavior:

- `is_published` is updated.
- If set to `true` and `published_at` is still `null`, `published_at` is set to `now()`.

---

## Public News API (no auth)

Public news listing and viewing is available for everyone, including unauthenticated users.

Prefix: `/api/v1/public/news`

### Public index

**GET** `/api/v1/public/news`

Returns only `is_published = true` news.

Query params:

- `q` — search in `title` and `short_description`
- `from` — `YYYY-MM-DD` (filter `published_at >= from`)
- `to` — `YYYY-MM-DD` (filter `published_at <= to`)

**Example:**

```text
GET /api/v1/public/news?q=api&from=2025-01-01&to=2025-12-31
```

Response: paginated list of published news.

---

### Public show

**GET** `/api/v1/public/news/{id}`

- `{id}` — ID of the news.
- Returns only published news (`is_published = true`) with:
  - author (id, name)
  - content blocks (ordered by `position`)

---
