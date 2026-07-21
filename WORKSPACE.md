# AI Workspace — Integrasi MCP ke Editor & Agent (Module 10)

Menghubungkan **Task Management MCP** ke berbagai AI client, plus studi kasus
workflow natural-language. Config siap-pakai ada di folder `clients/`.

Dua alamat server yang dipakai di sepanjang dokumen ini:

- **stdio**: `php artisan mcp:start task-management` (di-spawn client, tanpa server nyala)
- **HTTP**: `http://127.0.0.1:8000/mcp/task-management` (server `php artisan serve`, OAuth 2.1)

---

## 1. Cheatsheet: cara `add` per client

Tiap CLI/GUI punya sintaks berbeda. Ini yang paling sering bikin bingung.

### Claude Code (CLI)
```bash
# HTTP / URL
claude mcp add --transport http task-management http://127.0.0.1:8000/mcp/task-management
# stdio
claude mcp add task-management -- php /abs/path/artisan mcp:start task-management
claude mcp list          # cek status
/mcp                     # (dalam sesi) lihat tools
```

### Codex (CLI)
```bash
# WAJIB pakai --url untuk HTTP (tanpa itu dianggap COMMAND/stdio)
codex mcp add task-management --url http://127.0.0.1:8000/mcp/task-management
codex mcp login task-management     # OAuth (server harus nyala)
# stdio
codex mcp add task-management -- php /abs/path/artisan mcp:start task-management
codex mcp list
```

### Cursor (GUI, file)
`.cursor/mcp.json` → gunakan `mcpServers` dengan `command`/`args` (stdio) atau `url` (HTTP).
Lihat `clients/cursor.mcp.json`.

### VS Code (Copilot Agent, file)
`.vscode/mcp.json` → gunakan key **`servers`** (bukan `mcpServers`), dengan
`"type": "stdio" | "http"`. Token via blok `inputs`. Lihat `clients/vscode.mcp.json`.

### Claude Desktop (GUI, file)
`claude_desktop_config.json` → `mcpServers` (stdio-only). Untuk URL pakai jembatan
`npx mcp-remote <url>`. Lihat `clients/claude_desktop.json`.

### Ringkasan perbedaan sintaks

| Client | Key config | HTTP | stdio |
|--------|-----------|------|-------|
| Claude Code | CLI | `--transport http <url>` | `-- <cmd>` |
| Codex | CLI/`config.toml` | `--url <url>` | `-- <cmd>` |
| Cursor | `mcpServers` | `"url": "..."` | `command`/`args` |
| VS Code | `servers` | `"type":"http"` | `"type":"stdio"` |
| Claude Desktop | `mcpServers` | via `mcp-remote` | `command`/`args` |

---

## 2. stdio vs HTTP — pilih yang mana?

| | stdio | HTTP/URL |
|---|---|---|
| Server harus nyala? | Tidak | **Ya** (`php artisan serve`) |
| Auth | Tidak (proses lokal terpercaya) | **OAuth 2.1** / Bearer token |
| Data yang terlihat AI | **semua** (unrestricted) | **hanya board milik/diikuti user** |
| Cocok untuk | dev lokal cepat | remote, multi-user, produksi |

> Karena scoping: lewat **stdio** AI melihat seluruh board; lewat **HTTP+OAuth**
> AI hanya melihat board milik user yang login. Untuk demo "AI atas nama user",
> gunakan HTTP.

## 3. Mendapatkan token (untuk HTTP tanpa OAuth interaktif)

```bash
php artisan mcp:token you@example.com          # user biasa
php artisan mcp:token admin@example.com --admin # admin (boleh delete)
```
Pakai sebagai header `Authorization: Bearer <token>` (lihat `clients/vscode.mcp.json`).

---

## 4. Studi kasus workflow (natural language)

Contoh perintah ke AI dan tool MCP yang terpanggil. Semua memakai **task code**
(mis. `SPR-1`) yang jauh lebih enak dirujuk daripada id.

### a. Perencanaan sprint
> "Buatkan board 'Rilis v2', lalu pecah goal 'launch billing' jadi 6 task prioritas campuran."

Tool: `create-board-tool` → `plan-board` (prompt) → `create-task-tool` ×6.

### b. Bulk import + progress
> "Impor task berikut ke board SPR: Setup CI, Write docs, Fix login, Add tests."

Tool: `bulk-create-tasks-tool` (streaming `notifications/progress`).

### c. Triage & pindah
> "Cari semua task prioritas high yang belum selesai, pindahkan ke board 'Urgent'."

Tool: `search-task-tool` / `list-tasks-tool` (filter) → `move-task-tool`.

### d. Kolaborasi tim
> "Undang budi@example.com ke board SPR, lalu assign SPR-3 dan SPR-5 ke Budi."

Tool: `invite-member-tool` → `assign-task-tool` ×2.
(Assign hanya berhasil ke **anggota board**.)

### e. "Task saya" (atas nama user login — butuh OAuth)
> "Task apa saja yang jadi tanggung jawab saya dan sudah lewat deadline?"

Tool: `my-tasks-tool` + `list-tasks-tool` (filter `overdue`).

### f. Standup harian
> "Buatkan ringkasan standup untuk board SPR buat stakeholder."

Tool: `standup` (prompt, menyisipkan hitungan task per status).

### g. Update cepat
> "Ubah deadline SPR-3 jadi 2026-08-01 dan naikkan prioritasnya ke high."

Tool: `update-task-tool`.

---

## 5. Tips membangun MCP untuk produksi

- **Deskripsi tool untuk AI, bukan manusia** — sebut aksi, nilai valid, dan apa yang
  dikembalikan. Referensikan entitas dengan **code** yang mudah diketik.
- **Anotasi keamanan** — tandai `#[IsReadOnly]` / `#[IsDestructive]` / `#[IsIdempotent]`
  agar client tahu risiko tiap tool.
- **Scoping per-user** — selalu batasi data ke `request->user()` di transport HTTP.
- **Batasi output** — pakai pagination (`list-tasks-tool`), jangan kirim ribuan baris.
- **Progress untuk operasi panjang** — `notifications/progress`.
- **Uji tanpa membuka editor** — `php artisan mcp:inspector task-management` atau test
  `Server::tool()`/`resource()`/`prompt()`.
- **Produksi** — HTTPS + OAuth wajib, rate limit, logging (lihat `DEPLOYMENT.md`).
