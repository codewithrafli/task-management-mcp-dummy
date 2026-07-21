# Rencana Slide (PPT) — Laravel MCP

Prinsip: **video ngoding = screencast** (tanpa/mininal slide), **video konsep = slide + diagram**.
Jadi jumlah deck jauh lebih sedikit dari jumlah video. Fokus bikin **~10 deck konsep**
+ template judul/ringkasan yang dipakai ulang.

---

## A. Template yang dipakai berulang (bikin sekali)

1. **Slide Judul** — nama section/lesson, nomor, logo.
2. **Slide Tujuan** — "Di video ini kamu akan…" (2–4 bullet).
3. **Slide Ringkasan/Recap** — poin kunci + "commit/branch checkpoint".
4. **Slide Perintah** — kotak command yang di-highlight (mis. `composer require laravel/mcp`).

> Untuk lesson ngoding, cukup pakai template 1–3. Sisanya screencast editor.

---

## B. Deck konsep yang WAJIB dibuat (dengan diagram)

### Deck 1 — Pengenalan MCP  *(Section 1)*
- Apa itu MCP & masalah yang diselesaikan (AI ↔ aplikasi)
- **Diagram:** Host/Client ↔ Server ↔ Data/Tools
- MCP Client vs MCP Server
- Contoh client: Claude Desktop, Cursor, VS Code, Codex
- Demo hasil akhir (screenshot)

### Deck 2 — Arsitektur & Transport  *(Section 1/3)*
- **Diagram:** request lifecycle (client → server → handle → response)
- JSON-RPC 2.0 singkat (request/response/notification)
- **Diagram perbandingan:** stdio vs HTTP (kapan pakai yang mana)
- Primitives: **Tools · Resources · Prompts** (satu slide per definisi + kapan dipakai)

### Deck 3 — Arsitektur Aplikasi (starter)  *(Section 2)*
- **ERD:** User – Board – Task – board_user (member) – assignee
- Lapisan: Model → Service → Policy → Livewire
- Konsep `code` task (SPR-1) & kepemilikan/member
- Kenapa app dipisah dari MCP

### Deck 4 — Anatomi Tool/Resource/Prompt  *(Section 4–6)*
- Struktur class: `handle()`, `schema()`, atribut `#[Description]`
- **Diagram:** input schema → validasi → service → Response
- Resource: URI & URI template
- Prompt: argument & message role

### Deck 5 — Testing MCP  *(Section 7)*
- Piramida: Inspector → stdio manual → test otomatis
- Anatomi test `Server::tool()->assertOk()`
- (Sisanya screencast)

### Deck 6 — Best Practice MCP  *(Section 9)*
- Ciri Tool yang baik (deskripsi untuk AI, code vs id)
- **Tabel:** annotations readOnly / destructive / idempotent
- Error handling & Response informatif
- Struktur folder scalable

### Deck 7 — OAuth 2.1 untuk MCP  *(Section 10)* ⭐ paling banyak diagram
- Kenapa OAuth (bukan token statis) untuk client publik
- **Sequence diagram:** Authorization Code + PKCE
  (discovery → register → authorize/consent → token → panggil MCP)
- Peran Passport; endpoint discovery (`.well-known/...`)
- **Diagram scoping:** user hanya lihat board miliknya/diikuti
- Permission per-tool (Gate) & rate limiting

### Deck 8 — Advanced Features  *(Section 11)*
- Pagination & filtering (kenapa jangan kirim ribuan baris)
- **Diagram:** dynamic resource (URI template → variabel)
- **Diagram:** long-running task + progress notification (stream)
- Parity: update-task, rename-board, members

### Deck 9 — Deployment  *(Section 12)*
- **Diagram topologi:** Internet → Nginx (TLS) → PHP-FPM → Laravel
- Kenapa buffering off untuk `/mcp` (SSE)
- Passport keys, cache, permission
- Logging & monitoring

### Deck 10 — AI Workspace & Integrasi  *(Section 13–14)*
- **Diagram:** satu server, banyak client
- **Tabel cheatsheet:** cara `add` per client (Claude/Codex/Cursor/VS Code)
- stdio vs HTTP per client
- Studi kasus workflow (alur perintah → tool terpanggil)

---

## C. Video yang cukup screencast (tanpa deck konsep)

Semua lesson "ngoding" di Section 4 (buat tiap tool), 5, 6, 8 (integrasi), 11
(implementasi fitur), dan langkah-langkah deploy di 12 — cukup template judul +
recap. Slide detail tidak perlu; layar editor + terminal sudah cukup.

---

## D. Aset visual prioritas (buat ini dulu — dipakai di banyak slide)

1. **Diagram arsitektur MCP** (client/server/transport) — Deck 1 & 2
2. **ERD aplikasi** — Deck 3
3. **Sequence OAuth 2.1 + PKCE** — Deck 7 (paling penting & paling sering ditanya)
4. **Diagram scoping per-user** — Deck 7
5. **Diagram progress notification (stream)** — Deck 8
6. **Topologi deployment** — Deck 9
7. **Tabel cheatsheet client** — Deck 10 (sumber: `WORKSPACE.md`)

> Tip: diagram bisa dibuat cepat pakai Figma/FigJam atau Mermaid. Sumber konten sudah
> ada di dokumen repo (MCP_SETUP, SECURITY, ADVANCED, WORKSPACE, DEPLOYMENT).
