# Task Management — Starter (Kelas Laravel MCP)

Ini **branch starter**: aplikasi Task Management (mirip Trello) yang **sudah jadi**,
**tanpa** layer MCP. Dipakai sebagai titik awal kelas — di sepanjang course kamu akan
membangun **MCP Server** di atas aplikasi ini sehingga bisa dikontrol AI Agent
(Claude, Cursor, VS Code, Codex).

> Branch `master` berisi versi **lengkap** (aplikasi + MCP server) sebagai referensi.

## Fitur aplikasi

- Autentikasi: login, register, wajib login
- **Board**: buat (modal), rename (owner-only), hapus
- **Anggota board**: undang lewat email, kelola member (owner-only)
- **Task**: buat, detail/edit (status, prioritas, due date, assignee), hapus
- **Drag & drop** antar kolom (status + posisi tersimpan)
- **Kode task** yang mudah dirujuk (mis. `SPR-1`)
- Assignee dibatasi ke anggota board
- Filter: Task saya / assignee / prioritas
- Otorisasi via Policy; UI Livewire 4 + Tailwind

## Menjalankan

```bash
composer install
npm install && npm run build

cp .env.example .env
php artisan key:generate

# Database — sesuaikan .env (SQLite/MySQL), lalu:
php artisan migrate:fresh --seed

php artisan serve   # http://127.0.0.1:8000
```

**Login demo:** `test@example.com` / `password` (dari seeder; juga ada `budi@` & `ani@`).

## Testing

```bash
php artisan test        # 28 test (service + Livewire)
./vendor/bin/pint       # code style
```

## Struktur inti

```
app/
├── Enums/            TaskStatus, TaskPriority
├── Http/
│   ├── Controllers/  AuthController
│   └── Requests/     Form Request (validasi)
├── Models/           Board, Task, User
├── Policies/         BoardPolicy (otorisasi)
└── Services/         BoardService, TaskService (logika bisnis)
resources/views/components/  Livewire single-file components (board-index, board-show)
```

## Langkah berikutnya (di kelas)

Kamu akan menambahkan MCP: `composer require laravel/mcp`, membuat Tools/Resources/
Prompts, registrasi server di `routes/ai.php`, lalu menghubungkannya ke AI Agent.
