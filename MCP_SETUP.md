# Task Management MCP Server — Setup Guide

Aplikasi Laravel Task Management (mirip Trello) yang di-expose sebagai **MCP Server**
sehingga bisa dikontrol oleh AI Agent (Claude Desktop, Cursor, dll).

## 1. Instalasi

```bash
composer install
cp .env.example .env        # sudah dikonfigurasi ke SQLite
php artisan key:generate
php artisan migrate:fresh --seed
```

## 2. Struktur MCP

| Komponen  | File                                        | Fungsi |
|-----------|---------------------------------------------|--------|
| Server    | `app/Mcp/Servers/TaskManagementServer.php`  | Registrasi tools, resources, prompts |
| Tools     | `app/Mcp/Tools/*`                           | Aksi: create board/task, update status, move, search |
| Resources | `app/Mcp/Resources/*`                       | Data read-only: boards, tasks |
| Prompts   | `app/Mcp/Prompts/PlanBoardPrompt.php`       | Template perencanaan task |
| Routes    | `routes/ai.php`                             | `Mcp::local()` (stdio) + `Mcp::web()` (HTTP) |

### Tools
- `create-board-tool` — buat board baru
- `create-task-tool` — buat task di board
- `update-task-status-tool` — ubah status (todo / in_progress / done)
- `move-task-tool` — pindah task antar board
- `search-task-tool` — cari task by keyword

## 3. Testing

### MCP Inspector (interaktif)
```bash
php artisan mcp:inspector task-management
```

### Manual (stdio JSON-RPC)
```bash
printf '%s\n' \
'{"jsonrpc":"2.0","id":1,"method":"initialize","params":{"protocolVersion":"2025-06-18","capabilities":{},"clientInfo":{"name":"t","version":"1"}}}' \
'{"jsonrpc":"2.0","method":"notifications/initialized"}' \
'{"jsonrpc":"2.0","id":2,"method":"tools/list"}' \
| php artisan mcp:start task-management
```

### HTTP endpoint
`POST /mcp/task-management` (jalankan `php artisan serve` dulu).

## 4. Integrasi Claude Desktop

Salin isi `claude_desktop_config.example.json` ke:
- **macOS:** `~/Library/Application Support/Claude/claude_desktop_config.json`
- **Windows:** `%APPDATA%\Claude\claude_desktop_config.json`

Sesuaikan path absolut ke `artisan`, lalu restart Claude Desktop.
Setelah terhubung, coba minta: *"Buatkan board 'Rilis v2' dan tambahkan 3 task dengan prioritas high."*
