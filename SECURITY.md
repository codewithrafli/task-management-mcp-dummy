# Authentication & Security — MCP Server (Module 7)

Materi ini mengamankan MCP Server: autentikasi endpoint HTTP, authorization
per-tool, rate limiting, dan desain tool yang aman.

> Transport **stdio** (Claude Desktop lokal) berjalan sebagai proses milik user,
> jadi tidak perlu token. Yang diamankan adalah transport **HTTP** (`/mcp/*`)
> yang bisa diakses dari jaringan.

---

## 1. Autentikasi dengan Sanctum (token)

Endpoint HTTP dilindungi middleware `auth:sanctum`:

```php
// routes/ai.php
Mcp::web('/mcp/task-management', TaskManagementServer::class)
    ->middleware(['auth:sanctum', 'throttle:mcp']);
```

`User` memakai trait `HasApiTokens`. Terbitkan token dengan artisan command:

```bash
php artisan mcp:token you@example.com          # user biasa
php artisan mcp:token admin@example.com --admin # admin (boleh delete)
```

Kirim token pada tiap request:

```
Authorization: Bearer 1|xxxxxxrahasiaxxxxxx
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

## 4. Menghubungkan client via URL (dengan token)

`mcp-remote` bisa menyertakan header Authorization:

```json
{
  "mcpServers": {
    "task-management": {
      "command": "npx",
      "args": [
        "-y", "mcp-remote",
        "http://127.0.0.1:8000/mcp/task-management",
        "--header", "Authorization: Bearer 1|xxxxxxrahasiaxxxxxx"
      ]
    }
  }
}
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

## 6. Menuju OAuth 2.1 (lanjutan)

Sanctum cocok untuk token internal. Untuk client MCP publik (mis. konektor
Claude via URL), spesifikasi MCP menganjurkan **OAuth 2.1**. Package `laravel/mcp`
menyediakan `Mcp::oauthRoutes()` + Passport untuk discovery & authorization
server. Ini dibahas sebagai puncak Module 7 saat deploy publik (Module 9).

### Checklist produksi
- [ ] HTTPS wajib (jangan kirim Bearer token via HTTP polos)
- [ ] Rotasi & expiry token
- [ ] Rate limit + logging percobaan gagal
- [ ] Authorization eksplisit untuk setiap tool yang mengubah/menghapus data
- [ ] Batasi scope query (mis. filter `board_id`) agar AI tak mengakses data lain
