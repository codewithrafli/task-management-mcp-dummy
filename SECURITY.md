# Authentication & Security — MCP Server (Module 7)

Materi ini mengamankan MCP Server: autentikasi endpoint HTTP, authorization
per-tool, rate limiting, dan desain tool yang aman.

> Transport **stdio** (Claude Desktop lokal) berjalan sebagai proses milik user,
> jadi tidak perlu token. Yang diamankan adalah transport **HTTP** (`/mcp/*`)
> yang bisa diakses dari jaringan.

---

## 1. Autentikasi dengan OAuth 2.1 (Passport)

Sesuai spesifikasi MCP, endpoint HTTP diamankan dengan **OAuth 2.1** yang di-backing
oleh **Laravel Passport**. Endpoint dilindungi guard `auth:api`:

```php
// routes/ai.php
Mcp::oauthRoutes(); // discovery + dynamic client registration

Mcp::web('/mcp/task-management', TaskManagementServer::class)
    ->middleware(['auth:api', 'throttle:mcp']);
```

`User` memakai trait `Laravel\Passport\HasApiTokens`, dan guard `api` memakai
driver `passport` (`config/auth.php`).

### Dua cara mendapatkan access token

**a. Personal access token (untuk internal/testing)** — via artisan:

```bash
php artisan mcp:token you@example.com          # user biasa
php artisan mcp:token admin@example.com --admin # admin (boleh delete)
```

**b. Authorization Code + PKCE (untuk MCP client publik seperti Claude)** — client
mengikuti discovery otomatis (lihat §4). Tidak ada endpoint login manual; user login
& consent lewat halaman `oauth/authorize` milik Passport.

Kirim token pada tiap request:

```
Authorization: Bearer <access-token>
```

Tanpa token → **401 Unauthorized**. Dengan token valid → **200**.

> Agar 401 dikembalikan sebagai JSON (bukan redirect ke halaman login),
> path `mcp/*` dipaksa JSON di `bootstrap/app.php`:
> ```php
> $exceptions->shouldRenderJsonWhen(
>     fn (Request $request) => $request->is('api/*') || $request->is('mcp/*'),
> );
> ```

## 2. Authorization per-tool (Secure Tool Design)

Tool destruktif tidak cukup butuh "login" — butuh **izin**. Contoh
`DeleteTaskTool` hanya boleh dijalankan admin:

```php
// AppServiceProvider::boot()
Gate::define('delete-tasks', fn (User $user) => $user->is_admin);

// DeleteTaskTool::handle()
if (Gate::forUser($request->user())->denies('delete-tasks')) {
    return Response::error('You are not authorised to delete tasks.');
}
```

Prinsip:
- **Default deny** untuk aksi berisiko.
- Cek otorisasi **sebelum** menyentuh data.
- Kembalikan pesan error yang jelas, bukan diam-diam gagal.

## 3. Rate Limiting

Cegah abuse (AI bisa memanggil tool berkali-kali). Limiter `mcp`:

```php
// AppServiceProvider::boot()
RateLimiter::for('mcp', fn (Request $request) =>
    Limit::perMinute(60)->by($request->user()?->id ?: $request->ip())
);
```

Dipasang di route via `throttle:mcp`. Kunci per-user (fallback IP), sehingga
satu user nakal tidak memblokir yang lain.

## 4. Menghubungkan client via URL

### OAuth otomatis (rekomendasi untuk Claude/Cursor)

Client MCP modern cukup diberi URL server. Client akan:
1. Membaca `GET /.well-known/oauth-protected-resource` & `/.well-known/oauth-authorization-server`.
2. Mendaftar sendiri via `POST /oauth/register` (dynamic client registration).
3. Mengarahkan user ke `oauth/authorize` (login + consent, PKCE `S256`).
4. Menukar `code` menjadi access token di `oauth/token`.
5. Memanggil MCP dengan `Authorization: Bearer <token>`.

`mcp-remote` juga mendukung alur OAuth ini secara otomatis:

```json
{
  "mcpServers": {
    "task-management": {
      "command": "npx",
      "args": ["-y", "mcp-remote", "http://127.0.0.1:8000/mcp/task-management"]
    }
  }
}
```

### Header manual (personal access token)

Untuk testing cepat, sematkan token dari `php artisan mcp:token`:

```json
"args": [
  "-y", "mcp-remote",
  "http://127.0.0.1:8000/mcp/task-management",
  "--header", "Authorization: Bearer <access-token>"
]
```

Server Laravel harus nyala: `php artisan serve --port=8000`.

## 5. Testing keamanan

Lihat `tests/Feature/Mcp/`:
- `McpEndpointAuthTest` — tanpa token → 401, dengan token → 200.
- `DeleteTaskToolTest` — admin boleh hapus, non-admin ditolak (`assertHasErrors`).

```php
TaskManagementServer::actingAs($admin)
    ->tool(DeleteTaskTool::class, ['task_id' => $task->id])
    ->assertOk();
```

## 6. Setup OAuth 2.1 (langkah instalasi)

Untuk mereproduksi dari awal:

```bash
composer require laravel/passport
php artisan install:api --passport   # publish migrasi oauth + routes/api.php
php artisan migrate
php artisan passport:keys --force    # generate encryption keys
```

Lalu:
- `User` model: pakai `Laravel\Passport\HasApiTokens`.
- `config/auth.php`: guard `api` → driver `passport`.
- `routes/ai.php`: `Mcp::oauthRoutes()` + middleware `auth:api`.
- Personal access client dibuat otomatis oleh `DatabaseSeeder` (untuk `mcp:token`).

Discovery endpoint yang dihasilkan:
- `GET /.well-known/oauth-authorization-server`
- `GET /.well-known/oauth-protected-resource`
- `POST /oauth/register`, `oauth/authorize`, `oauth/token`

Scope MCP: `mcp:use`, PKCE: `S256`.

### Checklist produksi
- [ ] HTTPS wajib (jangan kirim Bearer token via HTTP polos)
- [ ] Rotasi & expiry token
- [ ] Rate limit + logging percobaan gagal
- [ ] Authorization eksplisit untuk setiap tool yang mengubah/menghapus data
- [ ] Batasi scope query (mis. filter `board_id`) agar AI tak mengakses data lain
