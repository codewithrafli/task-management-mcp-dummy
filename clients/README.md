# Config Client MCP

Config siap-pakai untuk menyambungkan **Task Management MCP** ke berbagai AI client.
Ganti `/absolute/path/to/mcp-dummy/artisan` dengan path absolut di mesinmu
(`pwd` di root project + `/artisan`).

| Client | File | Tujuan penyalinan |
|--------|------|-------------------|
| Cursor | `cursor.mcp.json` | `.cursor/mcp.json` (project) atau `~/.cursor/mcp.json` |
| VS Code (Copilot Agent) | `vscode.mcp.json` | `.vscode/mcp.json` |
| Claude Desktop | `claude_desktop.json` | `~/Library/Application Support/Claude/claude_desktop_config.json` |
| Codex CLI | `codex.config.toml` | `~/.codex/config.toml` (atau `codex mcp add`) |

Tiap file berisi **dua varian** — pilih salah satu:

- **stdio** — client menjalankan `php artisan mcp:start task-management`. Tanpa server
  nyala, tanpa auth. Paling gampang untuk lokal.
- **HTTP/URL** — client konek ke `http://127.0.0.1:8000/mcp/task-management`. Server
  **harus nyala** (`php artisan serve`) dan endpoint diproteksi **OAuth 2.1**; sebagian
  client bisa OAuth otomatis, sisanya butuh Bearer token (`php artisan mcp:token`).

Detail lengkap + cheatsheet CLI ada di `../WORKSPACE.md`.
