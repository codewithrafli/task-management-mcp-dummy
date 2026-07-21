# Kurikulum Video — Laravel MCP

Struktur pembelajaran (section → lesson). Durasi = estimasi. Kode mengikuti repo:
mulai dari branch **`starter`** (aplikasi Task Management jadi, tanpa MCP), lalu
membangun MCP Server di atasnya. Branch **`master`** = kunci jawaban.

Total: **14 section · 58 video · ± 8 jam**

---

## 🆓 MATERI GRATIS

### 1. Start Here
| Lesson | Durasi |
|--------|--------|
| Apa itu Model Context Protocol (MCP) | 10:00 |
| MCP Client vs MCP Server & cara AI bicara ke aplikasi | 9:00 |
| Demo hasil akhir (AI mengontrol Task Management) | 7:00 |
| Instalasi Tools (PHP, Composer, Node) | 10:00 |
| Instalasi & konfigurasi Git | 8:00 |
| Clone starter, run app & penjelasan struktur project | 12:00 |

### 2. Mengenal Aplikasi (starter)
| Lesson | Durasi |
|--------|--------|
| Tur fitur: board, task, member, drag & drop | 8:00 |
| Model, Migration & relasi (Board, Task, User, members) | 10:00 |
| Enum, Service Layer & Policy | 9:00 |
| Livewire single-file component & seeder | 8:00 |

### 3. Instalasi Laravel MCP
| Lesson | Durasi |
|--------|--------|
| `composer require laravel/mcp` & `mcp:install` | 6:00 |
| Anatomi: Server, Tool, Resource, Prompt | 8:00 |
| Membuat Server & registrasi di `routes/ai.php` | 7:00 |

### 4. Membuat Tools
| Lesson | Durasi |
|--------|--------|
| Tool pertama: Create Board (schema & handle) | 12:00 |
| Validasi & Form Request di dalam Tool | 9:00 |
| Create Task (schema kompleks: enum, nullable) | 11:00 |
| Update Status & Move Task | 9:00 |
| Search Task | 7:00 |
| Response type: text, json, error | 6:00 |

### 5. Membuat Resources
| Lesson | Durasi |
|--------|--------|
| Boards Resource (data read-only) | 8:00 |
| Tasks Resource + `#[Uri]` & `#[MimeType]` | 7:00 |

### 6. Membuat Prompts
| Lesson | Durasi |
|--------|--------|
| Plan Board Prompt (argument) | 8:00 |

### 7. Testing MCP
| Lesson | Durasi |
|--------|--------|
| MCP Inspector (`mcp:inspector`) | 9:00 |
| Uji stdio manual (JSON-RPC) | 7:00 |
| Test otomatis: `Server::tool()` + assertions | 12:00 |

### 8. Integrasi Claude Desktop
| Lesson | Durasi |
|--------|--------|
| Konfigurasi `claude_desktop_config.json` (stdio) | 8:00 |
| AI membuat & mencari task | 9:00 |
| AI update & pindah task | 7:00 |
| Konek via URL dengan `mcp-remote` | 8:00 |

### 9. Best Practice MCP
| Lesson | Durasi |
|--------|--------|
| Mendesain Tool yang baik (deskripsi, code vs id) | 10:00 |
| Error handling & Response yang informatif | 8:00 |
| Tool annotations (readOnly/destructive/idempotent) | 7:00 |
| Struktur project yang scalable | 6:00 |

---

## 🔒 MATERI PLUS

### 10. Authentication & Security
| Lesson | Durasi |
|--------|--------|
| Melindungi endpoint HTTP + rate limiting | 12:00 |
| Install Passport & setup OAuth 2.1 | 15:00 |
| Discovery, dynamic client registration & PKCE | 14:00 |
| Halaman login & consent (authorization view) | 12:00 |
| Alur penuh: authorize → token → panggil MCP | 13:00 |
| Permission per-tool (Gate) & secure delete | 10:00 |
| Scoping data per-user (owner & member) | 14:00 |

### 11. Advanced MCP Features
| Lesson | Durasi |
|--------|--------|
| Pagination & Filtering (List Tasks) | 11:00 |
| Dynamic Resource (URI template) | 10:00 |
| Prompt Template lanjutan (Standup) | 9:00 |
| Long-running task + Progress Notification | 13:00 |
| Parity tools: Update Task, Rename Board, Members | 12:00 |

### 12. Deploy MCP Server ke VPS
| Lesson | Durasi |
|--------|--------|
| Persiapan VPS (Nginx, PHP-FPM) | 14:00 |
| Deploy kode, Passport keys & permission | 12:00 |
| Nginx config + SSL/HTTPS (Certbot) | 13:00 |
| Logging & Monitoring MCP | 10:00 |

### 13. Membangun AI Workspace
| Lesson | Durasi |
|--------|--------|
| Integrasi Cursor | 8:00 |
| Integrasi VS Code Agent | 8:00 |
| Integrasi Codex CLI (`mcp add --url`) | 7:00 |
| Cheatsheet `mcp add` tiap client (stdio vs HTTP) | 9:00 |

### 14. Studi Kasus & Penutup
| Lesson | Durasi |
|--------|--------|
| Studi kasus automasi workflow (sprint, triage, standup) | 12:00 |
| Kolaborasi tim: invite & assign via AI | 8:00 |
| Tips membangun MCP untuk produksi | 9:00 |

---

## Catatan produksi

- Tiap section idealnya punya **checkpoint git** (tag/branch) agar murid bisa
  menyamakan progres.
- Referensi kode & dokumen: `MCP_SETUP.md`, `SECURITY.md`, `ADVANCED.md`,
  `WORKSPACE.md`, `DEPLOYMENT.md`, folder `clients/` & `deploy/`.
- Video "Prompting"/"QA" (opsional) — tunjukkan meminta AI melakukan tugas nyata
  lalu verifikasi hasilnya di UI dan via test.
