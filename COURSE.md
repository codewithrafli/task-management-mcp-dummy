# Kurikulum & Panduan Rekaman — Laravel MCP

Struktur pembelajaran (section → lesson) + panduan produksi per video. Durasi = estimasi.
Kode mengikuti repo: mulai dari branch **`starter`** (aplikasi Task Management jadi, tanpa
MCP), lalu membangun MCP Server di atasnya. Branch **`master`** = kunci jawaban.

**Total: 14 section · 58 video · ± 8 jam**

Legend kartu tiap lesson:

- **🎯 Tujuan** — hasil yang dicapai di video ini
- **📊 Slide** — cara membaca & menjelaskan slide (untuk video berbasis slide)
- **🎬 Rekam** — langkah konkret + command yang dilakukan di layar
- **📁 File** — file/lokasi kode yang disentuh
- **💬 Poin** — inti yang harus ditekankan / jebakan umum

---

## 0. Persiapan Rekaman (baca sekali)

**Environment**

- PHP 8.4, Composer, Node 20+, Git, editor (VS Code / PhpStorm), Claude Desktop.
- DB: SQLite (paling gampang) atau MySQL — sebutkan pilihannya di awal.
- Terminal: font besar (16–18px), tema kontras tinggi, window bersih.

**Editor**

- Font 16–18px, zoom cukup agar kode terbaca di 1080p.
- Sembunyikan panel yang tak perlu; tampilkan file tree saat pindah file.
- Aktifkan format-on-save (Pint) agar kode selalu rapi saat direkam.

**Alur baku tiap video**

1. Buka dengan 1 kalimat tujuan ("Di video ini kita akan…").
2. Tunjukkan hasil akhir dulu (±10 detik) bila memungkinkan.
3. Koding/demo pelan — jelaskan **kenapa**, bukan hanya **apa**.
4. Verifikasi (browser, `php artisan test`, atau MCP Inspector).
5. Tutup dengan recap 1 kalimat + "di video berikutnya…".

**Cara membaca slide (untuk video konsep)**

- Jangan membaca slide kata-per-kata. Baca **judul/pill**, lalu jelaskan tiap poin
  dengan kalimatmu sendiri + 1 contoh nyata dari aplikasi (mis. "SPR-1", "board Sprint").
- Satu poin = satu napas: sebutkan poin → kasih contoh → lanjut. Jangan menumpuk.
- Tutup slide dengan 1 kalimat "jadi intinya…" sebelum pindah ke koding/slide berikutnya.

**Checkpoint git** — buat tag per section: `s01-intro`, `s03-install`, `s04-tools`, `s07-testing`, `s10-oauth`, dst.

**Reset data demo:** `php artisan migrate:fresh --seed` · **Login demo:** `test@example.com` / `password`

---

# 🆓 MATERI GRATIS

## 1. Start Here

| Lesson | Durasi |
|--------|--------|
| Apa itu Model Context Protocol (MCP) | 10:00 |
| MCP Client vs MCP Server & cara AI bicara ke aplikasi | 9:00 |
| Demo hasil akhir (AI mengontrol Task Management) | 7:00 |
| Instalasi Tools (PHP, Composer, Node) | 10:00 |
| Instalasi & konfigurasi Git | 8:00 |
| Clone starter, run app & penjelasan struktur project | 12:00 |

**▸ Apa itu Model Context Protocol (MCP) — 10:00**

- **🎯 Tujuan:** membangun mental model MCP sebelum menyentuh kode.
- **📊 Slide "Apa Itu MCP":** baca judul, lalu jelaskan: "MCP itu protokol terbuka —
  anggap saja **USB-C untuk AI**: satu colokan standar biar AI Agent bisa nyambung ke
  aplikasi apa pun." Tekankan kalimat kunci di slide: AI **tidak hanya membaca** data,
  tapi bisa **menjalankan aksi** (membuat, mengubah, mencari) lewat Tools/Resources/Prompts.
  Kasih contoh: "nanti kita bisa bilang ke Claude 'buatkan 3 task', dan dia benar-benar
  membuatnya di aplikasi kita."
- **🎬 Rekam:** tampilkan slide + diagram client↔server. Belum ada koding.
- **💬 Poin:** MCP = jembatan standar AI ↔ aplikasi. Tanpa MCP, tiap integrasi harus custom.

**▸ MCP Client vs MCP Server & cara AI bicara — 9:00**

- **🎯 Tujuan:** membedakan peran client & server serta cara komunikasinya.
- **📊 Slide "3 Komponen MCP":** jelaskan dua sisi — **Client** (Claude, Cursor, VS Code,
  Codex) yang memanggil, dan **Server** (aplikasi Laravel kita) yang menyediakan.
  Gambar alur di layar: client kirim request → server `handle()` → kembalikan response.
  Sebut protokolnya **JSON-RPC 2.0**, dan tekankan "**satu server bisa dipakai banyak
  client** sekaligus".
- **🎬 Rekam:** slide + animasi/gambar alur JSON-RPC sederhana.
- **💬 Poin:** kita membangun sisi **Server**; client-nya tinggal pilih.

**▸ Demo hasil akhir — 7:00**

- **🎯 Tujuan:** memperlihatkan tujuan akhir supaya penonton termotivasi.
- **🎬 Rekam:** buka Claude Desktop yang sudah tersambung ke branch `master`. Minta:
  "Buat board 'Sprint' + 3 task prioritas high", lalu "assign SPR-1 ke Budi, pindahkan ke
  Done". Pindah ke UI web, refresh, tunjukkan perubahannya nyata.
- **💬 Poin:** "semua ini yang akan kita bangun dari nol di kelas ini."

**▸ Instalasi Tools (PHP, Composer, Node) — 10:00**

- **🎯 Tujuan:** menyamakan environment semua murid.
- **🎬 Rekam:** cek versi (`php -v`, `composer -V`, `node -v`); instal yang kurang.
- **💬 Poin:** sebutkan versi minimum; beri catatan singkat Windows/Mac/Linux.

**▸ Instalasi & konfigurasi Git — 8:00**

- **🎯 Tujuan:** siap clone repo & memakai checkpoint.
- **🎬 Rekam:** `git --version`, set `user.name`/`user.email`, login GitHub.
- **💬 Poin:** jelaskan guna branch `starter` (titik awal) vs `master` (kunci jawaban).

**▸ Clone starter, run app & struktur project — 12:00**

- **🎯 Tujuan:** aplikasi jalan di lokal murid.
- **🎬 Rekam:** `git clone` → `git checkout starter` → `composer install` →
  `npm install && npm run build` → `cp .env.example .env` → `php artisan key:generate` →
  `php artisan migrate:fresh --seed` → `php artisan serve`. Login demo, keliling app.
- **📁 File:** `README.md`, `.env`.
- **💬 Poin:** app SUDAH jadi — fokus kelas 100% pada MCP.

## 2. Mengenal Aplikasi (starter)

| Lesson | Durasi |
|--------|--------|
| Tur fitur: board, task, member, drag & drop | 8:00 |
| Model, Migration & relasi (Board, Task, User, members) | 10:00 |
| Enum, Service Layer & Policy | 9:00 |
| Livewire single-file component & seeder | 8:00 |

**▸ Tur fitur: board, task, member, drag & drop — 8:00**

- **🎯 Tujuan:** memahami domain aplikasi.
- **🎬 Rekam:** buat board (modal), buat task, buka detail task, drag antar kolom,
  undang member, coba filter.
- **📁 File:** `resources/views/components/⚡board-*.blade.php`.
- **💬 Poin:** sorot konsep **code task** (SPR-1) & kepemilikan/anggota board.

**▸ Model, Migration & relasi — 10:00**

- **🎯 Tujuan:** membaca skema data.
- **🎬 Rekam:** telusuri migration `boards`, `tasks`, `board_user`; model & relasi & scope.
- **📁 File:** `database/migrations/*`, `app/Models/*`.
- **💬 Poin:** Board hasMany Task; Board belongsToMany User (member); Task punya assignee & `code`.

**▸ Enum, Service Layer & Policy — 9:00**

- **🎯 Tujuan:** memahami lapisan bisnis & otorisasi.
- **🎬 Rekam:** `TaskStatus`/`TaskPriority`; `BoardService`/`TaskService`; `BoardPolicy`.
- **📁 File:** `app/Enums/*`, `app/Services/*`, `app/Policies/BoardPolicy.php`.
- **💬 Poin:** logika di Service (dipakai UI & nanti MCP), otorisasi di Policy.

**▸ Livewire single-file component & seeder — 8:00**

- **🎯 Tujuan:** memahami UI & data demo.
- **🎬 Rekam:** bedah `⚡board-show` (mount/authorize, addTask, reorderColumn); `DatabaseSeeder`.
- **📁 File:** `resources/views/components/⚡board-show.blade.php`, `database/seeders/DatabaseSeeder.php`.
- **💬 Poin:** Livewire 4 SFC; seeder membuat user & board demo.

## 3. Instalasi Laravel MCP

| Lesson | Durasi |
|--------|--------|
| `composer require laravel/mcp` & `mcp:install` | 6:00 |
| Anatomi: Server, Tool, Resource, Prompt | 8:00 |
| Membuat Server & registrasi di `routes/ai.php` | 7:00 |

**▸ `composer require laravel/mcp` & `mcp:install` — 6:00**

- **🎯 Tujuan:** memasang package MCP.
- **🎬 Rekam:** `composer require laravel/mcp` → `php artisan mcp:install`; tunjukkan
  `routes/ai.php` & folder `app/Mcp` yang terbentuk.
- **📁 File:** `routes/ai.php`.
- **💬 Poin:** jelaskan apa yang di-generate & buat apa.

**▸ Anatomi: Server, Tool, Resource, Prompt — 8:00**

- **🎯 Tujuan:** memberi peta besar sebelum koding.
- **📊 Slide "3 Komponen MCP":** baca tiap komponen sambil tunjuk foldernya di editor:
  **Tools** = aksi yang dijalankan AI; **Resources** = data read-only untuk dibaca AI;
  **Prompts** = template instruksi siap pakai; **Server** = yang merangkai & mendaftarkan
  semuanya di `routes/ai.php`. Analogi: Server itu "buku menu", Tool = "pesanan yang bisa
  dibuat".
- **🎬 Rekam:** slide + buka struktur `app/Mcp/{Servers,Tools,Resources,Prompts}`.
- **💬 Poin:** ingat perbedaan Tool (aksi) vs Resource (baca).

**▸ Membuat Server & registrasi di `routes/ai.php` — 7:00**

- **🎯 Tujuan:** server pertama hidup.
- **🎬 Rekam:** `make:mcp-server TaskManagementServer`; isi `#[Name]`/`#[Instructions]`;
  daftarkan `Mcp::local('task-management', ...)`; uji `tools/list` (masih kosong) via `mcp:start`.
- **📁 File:** `app/Mcp/Servers/TaskManagementServer.php`, `routes/ai.php`.
- **💬 Poin:** mulai dari transport **stdio**; HTTP menyusul di Section 10.

## 4. Membuat Tools

| Lesson | Durasi |
|--------|--------|
| Tool pertama: Create Board (schema & handle) | 12:00 |
| Validasi & Form Request di dalam Tool | 9:00 |
| Create Task (schema kompleks: enum, nullable) | 11:00 |
| Update Status & Move Task | 9:00 |
| Search Task | 7:00 |
| Response type: text, json, error | 6:00 |

**▸ Tool pertama: Create Board — 12:00**

- **🎯 Tujuan:** membuat & memanggil tool pertama.
- **🎬 Rekam:** `make:mcp-tool CreateBoardTool`; isi `#[Description]`, `schema()`,
  `handle()` (validasi → `BoardService` → `Response::text`); daftarkan di server; uji via MCP Inspector.
- **📁 File:** `app/Mcp/Tools/CreateBoardTool.php`.
- **💬 Poin:** deskripsi ditulis **untuk AI**; kembalikan pesan yang informatif.

**▸ Validasi & Form Request di dalam Tool — 9:00**

- **🎯 Tujuan:** validasi satu sumber kebenaran.
- **🎬 Rekam:** buat `StoreBoardRequest`; pakai `$request->validate((new StoreBoardRequest)->rules())`.
- **📁 File:** `app/Http/Requests/StoreBoardRequest.php`.
- **💬 Poin:** aturan dipusatkan; MCP Request ≠ HTTP Request (tak bisa di-inject seperti controller).

**▸ Create Task (schema kompleks) — 11:00**

- **🎯 Tujuan:** menulis schema dengan enum & field nullable.
- **🎬 Rekam:** `CreateTaskTool` (board_id, title, description, status, priority, due_date,
  assignee_id); enum via `TaskStatus::values()`.
- **📁 File:** `app/Mcp/Tools/CreateTaskTool.php`, `StoreTaskRequest`.
- **💬 Poin:** tunjukkan `code` task otomatis (SPR-1) di response.

**▸ Update Status & Move Task — 9:00**

- **🎯 Tujuan:** aksi yang mengubah data.
- **🎬 Rekam:** `UpdateTaskStatusTool` & `MoveTaskTool`; rujuk task pakai `task` (code/id)
  via `Task::resolveRef`.
- **💬 Poin:** merujuk pakai **code** (SPR-1) jauh lebih enak untuk AI daripada id.

**▸ Search Task — 7:00**

- **🎯 Tujuan:** aksi baca terfilter.
- **🎬 Rekam:** `SearchTaskTool` (query + board_id opsional); tandai `#[IsReadOnly]`.
- **📁 File:** `app/Mcp/Tools/SearchTaskTool.php`.
- **💬 Poin:** kembalikan daftar ringkas + code.

**▸ Response type: text, json, error — 6:00**

- **🎯 Tujuan:** menguasai tipe balikan.
- **🎬 Rekam:** contoh `Response::text`, `Response::json`, `Response::error`.
- **💬 Poin:** kapan pakai masing-masing; error harus **actionable** (bisa dikoreksi AI).

## 5. Membuat Resources

| Lesson | Durasi |
|--------|--------|
| Boards Resource (data read-only) | 8:00 |
| Tasks Resource + `#[Uri]` & `#[MimeType]` | 7:00 |

**▸ Boards Resource — 8:00**

- **🎯 Tujuan:** data read-only pertama.
- **🎬 Rekam:** `make:mcp-resource BoardsResource`; `handle()` kembalikan JSON daftar board;
  daftarkan; uji `resources/read`.
- **📁 File:** `app/Mcp/Resources/BoardsResource.php`.
- **💬 Poin:** Resource untuk **membaca**, Tool untuk **aksi**.

**▸ Tasks Resource + `#[Uri]` & `#[MimeType]` — 7:00**

- **🎯 Tujuan:** metadata resource yang benar.
- **🎬 Rekam:** `TasksResource` dengan `#[Uri('tasks://tasks')]`, `#[MimeType('application/json')]`.
- **📁 File:** `app/Mcp/Resources/TasksResource.php`.
- **💬 Poin:** cek `uri`/`mimeType` muncul di `resources/list`.

## 6. Membuat Prompts

| Lesson | Durasi |
|--------|--------|
| Plan Board Prompt (argument) | 8:00 |

**▸ Plan Board Prompt — 8:00**

- **🎯 Tujuan:** membuat template instruksi.
- **🎬 Rekam:** `make:mcp-prompt PlanBoardPrompt`; `arguments()` (goal) + `handle()`; uji `prompts/get`.
- **📁 File:** `app/Mcp/Prompts/PlanBoardPrompt.php`.
- **💬 Poin:** Prompt muncul sebagai **slash command** di client.

## 7. Testing MCP

| Lesson | Durasi |
|--------|--------|
| MCP Inspector (`mcp:inspector`) | 9:00 |
| Uji stdio manual (JSON-RPC) | 7:00 |
| Test otomatis: `Server::tool()` + assertions | 12:00 |

**▸ MCP Inspector — 9:00**

- **🎯 Tujuan:** menguji secara interaktif.
- **📊 Slide "3 Cara Menguji MCP":** perkenalkan piramida uji — **Inspector** (paling
  gampang, GUI) → **stdio manual** (paham protokol) → **test otomatis** (paling andal).
  Sebut kapan pakai masing-masing.
- **🎬 Rekam:** `php artisan mcp:inspector task-management`; jalankan tiap tool/resource/prompt dari GUI.
- **💬 Poin:** cara tercepat lihat schema & hasil tanpa membuka client.

**▸ Uji stdio manual (JSON-RPC) — 7:00**

- **🎯 Tujuan:** memahami protokol di balik layar.
- **🎬 Rekam:** pipe `initialize` + `tools/list` + `tools/call` ke `php artisan mcp:start task-management`.
- **💬 Poin:** tunjukkan bentuk request/response JSON-RPC yang asli.

**▸ Test otomatis: `Server::tool()` + assertions — 12:00**

- **🎯 Tujuan:** regression aman.
- **🎬 Rekam:** Pest test `TaskManagementServer::tool(CreateTaskTool::class, [...])->assertOk()->assertSee(...)`,
  `assertHasErrors()`; jalankan `php artisan test`.
- **📁 File:** `tests/Feature/Mcp/*`.
- **💬 Poin:** uji happy path, input invalid, dan otorisasi.

## 8. Integrasi Claude Desktop

| Lesson | Durasi |
|--------|--------|
| Konfigurasi `claude_desktop_config.json` (stdio) | 8:00 |
| AI membuat & mencari task | 9:00 |
| AI update & pindah task | 7:00 |
| Konek via URL dengan `mcp-remote` | 8:00 |

**▸ Konfigurasi `claude_desktop_config.json` (stdio) — 8:00**

- **🎯 Tujuan:** menyambungkan Claude Desktop.
- **🎬 Rekam:** edit config (macOS/Windows path); command `php`, args `[abs/artisan, mcp:start, task-management]`; restart Claude.
- **📁 File:** `clients/claude_desktop.json`.
- **💬 Poin:** pakai path `php` absolut bila perlu.

**▸ AI membuat & mencari task — 9:00**

- **🎯 Tujuan:** demo aksi nyata.
- **🎬 Rekam:** minta Claude buat board + task, lalu cari; verifikasi di UI web.
- **💬 Poin:** deskripsi tool yang baik → AI memilih tool yang tepat.

**▸ AI update & pindah task — 7:00**

- **🎯 Tujuan:** aksi mutasi.
- **🎬 Rekam:** "Pindahkan SPR-1 ke Done", "assign ke Budi"; cek papan berubah.
- **💬 Poin:** referensi task via code.

**▸ Konek via URL dengan `mcp-remote` — 8:00**

- **🎯 Tujuan:** transport HTTP.
- **🎬 Rekam:** `php artisan serve`; config `npx mcp-remote http://127.0.0.1:8000/mcp/task-management`.
- **💬 Poin:** beda stdio vs HTTP; server harus nyala.

## 9. Best Practice MCP

| Lesson | Durasi |
|--------|--------|
| Mendesain Tool yang baik (deskripsi, code vs id) | 10:00 |
| Error handling & Response yang informatif | 8:00 |
| Tool annotations (readOnly/destructive/idempotent) | 7:00 |
| Struktur project yang scalable | 6:00 |

**▸ Mendesain Tool yang baik — 10:00**

- **🎯 Tujuan:** tool yang ramah-AI.
- **🎬 Rekam:** bandingkan deskripsi buruk vs baik; rujuk pakai code; satu tool satu aksi.
- **💬 Poin:** deskripsi ditulis **untuk AI**, bukan manusia.

**▸ Error handling & Response informatif — 8:00**

- **🎯 Tujuan:** gagal dengan anggun.
- **🎬 Rekam:** `findOrFail`, `Response::error` pesan jelas, validasi dulu baru eksekusi.
- **💬 Poin:** jangan bocorkan exception mentah ke client.

**▸ Tool annotations — 7:00**

- **🎯 Tujuan:** memberi hint keamanan.
- **📊 Slide "Tool Annotations":** jelaskan tiga tanda — `#[IsReadOnly]` (hanya membaca),
  `#[IsDestructive]` (menghapus), `#[IsIdempotent]` (aman diulang). Tekankan gunanya: bikin
  client/AI **paham risiko** tiap tool sebelum menjalankannya.
- **🎬 Rekam:** tambahkan annotations ke tool; cek muncul di `tools/list`.
- **💬 Poin:** anotasi = "label bahaya" untuk tiap aksi.

**▸ Struktur project scalable — 6:00**

- **🎯 Tujuan:** rapi saat proyek membesar.
- **🎬 Rekam:** kelompokkan tools per domain; peran Server & `#[Instructions]`.
- **💬 Poin:** Service tetap tipis; tool cukup jadi adapter.

---

# 🔒 MATERI PLUS

## 10. Authentication & Security

| Lesson | Durasi |
|--------|--------|
| Melindungi endpoint HTTP + rate limiting | 12:00 |
| Install Passport & setup OAuth 2.1 | 15:00 |
| Discovery, dynamic client registration & PKCE | 14:00 |
| Halaman login & consent (authorization view) | 12:00 |
| Alur penuh: authorize → token → panggil MCP | 13:00 |
| Permission per-tool (Gate) & secure delete | 10:00 |
| Scoping data per-user (owner & member) | 14:00 |

**▸ Melindungi endpoint HTTP + rate limiting — 12:00**

- **🎯 Tujuan:** endpoint tidak telanjang.
- **📊 Slide "Kenapa Perlu Keamanan":** endpoint HTTP bisa diakses siapa saja & tool bisa
  mengubah/menghapus data — jadi butuh **auth + otorisasi + rate limit + scoping**.
- **🎬 Rekam:** `Mcp::web(...)->middleware(['auth:...','throttle:mcp'])`; definisikan limiter `mcp` di `AppServiceProvider`.
- **📁 File:** `routes/ai.php`, `app/Providers/AppServiceProvider.php`.
- **💬 Poin:** tanpa proteksi, siapa pun bisa memanggil tool destruktif.

**▸ Install Passport & setup OAuth 2.1 — 15:00**

- **🎯 Tujuan:** menyiapkan OAuth server.
- **📊 Slide "OAuth 2.1 dengan Passport":** baca 4 poin — discovery otomatis, dynamic client
  registration, Authorization Code + PKCE, access token diverifikasi guard `auth:api`.
  Jelaskan **kenapa OAuth**, bukan token statis: untuk client publik seperti Claude connector.
- **🎬 Rekam:** `composer require laravel/passport` → `install:api --passport` → `migrate` →
  `passport:keys`; User pakai `HasApiTokens`; guard `api` = passport.
- **📁 File:** `config/auth.php`, `app/Models/User.php`.
- **💬 Poin:** OAuth = cara aman client publik dapat izin tanpa berbagi password.

**▸ Discovery, dynamic client registration & PKCE — 14:00**

- **🎯 Tujuan:** memahami standar auth MCP.
- **🎬 Rekam:** `Mcp::oauthRoutes()`; buka `.well-known/oauth-authorization-server`, `oauth/register`.
- **💬 Poin:** discovery bikin client menemukan endpoint sendiri; PKCE (`S256`) mencegah pencurian code.

**▸ Halaman login & consent (authorization view) — 12:00**

- **🎯 Tujuan:** menyediakan UI otorisasi.
- **🎬 Rekam:** `Passport::authorizationView('oauth.authorize')`; buat blade consent; tambah
  login page (AuthController + route).
- **📁 File:** `resources/views/oauth/authorize.blade.php`, `app/Http/Controllers/AuthController.php`.
- **💬 Poin:** Passport 13 **tak menyertakan view** consent → ini penyebab error umum, wajib dibuat.

**▸ Alur penuh: authorize → token → panggil MCP — 13:00**

- **🎯 Tujuan:** membuktikan end-to-end.
- **📊 Slide "Alur OAuth Singkat":** ikuti 4 langkah di slide — client baca discovery &
  daftar otomatis → user login & consent → tukar `code` jadi access token (PKCE) → panggil
  MCP dengan Bearer token.
- **🎬 Rekam:** login → consent → dapat `code` → tukar `oauth/token` → panggil `/mcp/...`
  dengan Bearer → **200**. Bisa via `codex mcp login` / `mcp-remote`.
- **💬 Poin:** tunjukkan **401 tanpa token, 200 dengan token**.

**▸ Permission per-tool (Gate) & secure delete — 10:00**

- **🎯 Tujuan:** aksi destruktif terlindungi.
- **🎬 Rekam:** Gate `delete-tasks` (admin); `DeleteTaskTool` cek `Gate::denies`.
- **📁 File:** `app/Mcp/Tools/DeleteTaskTool.php`.
- **💬 Poin:** default-deny untuk aksi berbahaya.

**▸ Scoping data per-user (owner & member) — 14:00**

- **🎯 Tujuan:** user hanya melihat datanya.
- **🎬 Rekam:** trait `InteractsWithBoards` (boardsQuery/tasksQuery/resolve*); pasang di resource & semua tool.
- **📁 File:** `app/Mcp/Concerns/InteractsWithBoards.php`.
- **💬 Poin:** stdio (lokal) bebas; HTTP (OAuth) **ter-scope** ke board milik/diikuti user.

## 11. Advanced MCP Features

| Lesson | Durasi |
|--------|--------|
| Pagination & Filtering (List Tasks) | 11:00 |
| Dynamic Resource (URI template) | 10:00 |
| Prompt Template lanjutan (Standup) | 9:00 |
| Long-running task + Progress Notification | 13:00 |
| Parity tools: Update Task, Rename Board, Members | 12:00 |

**▸ Pagination & Filtering — 11:00**

- **🎯 Tujuan:** jangan kirim ribuan baris ke AI.
- **📊 Slide "Fitur Lanjutan":** perkenalkan 4 fitur lanjutan (pagination, dynamic resource,
  prompt lanjutan, progress notification) — video ini bahas yang pertama.
- **🎬 Rekam:** `ListTasksTool` (filter board/status/priority/assignee/overdue + page/per_page) → JSON data + pagination.
- **📁 File:** `app/Mcp/Tools/ListTasksTool.php`.
- **💬 Poin:** sertakan `has_more` agar AI tahu masih ada halaman.

**▸ Dynamic Resource (URI template) — 10:00**

- **🎯 Tujuan:** resource ber-parameter.
- **🎬 Rekam:** `BoardTasksResource implements HasUriTemplate` → `board://{boardId}/tasks`.
- **📁 File:** `app/Mcp/Resources/BoardTasksResource.php`.
- **💬 Poin:** variabel URI otomatis masuk ke `$request->get()`.

**▸ Prompt Template lanjutan (Standup) — 9:00**

- **🎯 Tujuan:** prompt multi-argument + data nyata.
- **🎬 Rekam:** `StandupPrompt` (board, audience) menyisipkan hitungan task per status.
- **📁 File:** `app/Mcp/Prompts/StandupPrompt.php`.
- **💬 Poin:** prompt bisa cerdas dengan data dari DB.

**▸ Long-running task + Progress Notification — 13:00**

- **🎯 Tujuan:** streaming progres untuk operasi panjang.
- **🎬 Rekam:** `BulkCreateTasksTool` return `Generator`; `yield Response::notification('notifications/progress', ...)`;
  uji via stdio + `_meta.progressToken`.
- **📁 File:** `app/Mcp/Tools/BulkCreateTasksTool.php`.
- **💬 Poin:** kirim progress **hanya jika** client menyertakan `progressToken`.

**▸ Parity tools: Update Task, Rename Board, Members — 12:00**

- **🎯 Tujuan:** MCP setara aplikasi.
- **🎬 Rekam:** `UpdateTaskTool`, `RenameBoardTool`, `ListMembersTool`, `InviteMemberTool`;
  assignee dibatasi anggota board.
- **💬 Poin:** jaga konsistensi UI ↔ AI (apa yang bisa di UI, bisa juga via AI).

## 12. Deploy MCP Server ke VPS

| Lesson | Durasi |
|--------|--------|
| Persiapan VPS (Nginx, PHP-FPM) | 14:00 |
| Deploy kode, Passport keys & permission | 12:00 |
| Nginx config + SSL/HTTPS (Certbot) | 13:00 |
| Logging & Monitoring MCP | 10:00 |

**▸ Persiapan VPS (Nginx, PHP-FPM) — 14:00**

- **🎯 Tujuan:** server siap.
- **📊 Slide "Deploy ke VPS":** ringkas alur produksi — Nginx + PHP-FPM melayani endpoint,
  HTTPS wajib, Passport keys diamankan, buffering off untuk `/mcp` (SSE).
- **🎬 Rekam:** install nginx/php-fpm/composer; clone; `composer install --no-dev`.
- **📁 File:** `DEPLOYMENT.md`.
- **💬 Poin:** endpoint MCP = route Laravel biasa, dilayani web server yang sama.

**▸ Deploy kode, Passport keys & permission — 12:00**

- **🎯 Tujuan:** aplikasi live.
- **🎬 Rekam:** `.env` production, `key:generate`, `migrate --force`, `passport:keys`,
  chmod storage & `oauth-*.key` 600, `config/route/view:cache`.
- **💬 Poin:** amankan kunci OAuth (jangan ter-commit, mode 600).

**▸ Nginx config + SSL/HTTPS (Certbot) — 13:00**

- **🎯 Tujuan:** HTTPS + streaming.
- **🎬 Rekam:** pasang `deploy/nginx.conf`; `certbot --nginx`; catatan buffering off untuk `/mcp`.
- **📁 File:** `deploy/nginx.conf`.
- **💬 Poin:** SSE (progress notification) butuh unbuffered.

**▸ Logging & Monitoring MCP — 10:00**

- **🎯 Tujuan:** observability.
- **🎬 Rekam:** channel `mcp` (`storage/logs/mcp.log`); `Log::channel('mcp')`; `tail -f`.
- **📁 File:** `config/logging.php`.
- **💬 Poin:** agregasi (Sentry/Loki) untuk produksi.

## 13. Membangun AI Workspace

| Lesson | Durasi |
|--------|--------|
| Integrasi Cursor | 8:00 |
| Integrasi VS Code Agent | 8:00 |
| Integrasi Codex CLI (`mcp add --url`) | 7:00 |
| Cheatsheet `mcp add` tiap client (stdio vs HTTP) | 9:00 |

**▸ Integrasi Cursor — 8:00**

- **🎯 Tujuan:** Cursor tersambung.
- **🎬 Rekam:** `.cursor/mcp.json` (pakai `url` atau `command`).
- **📁 File:** `clients/cursor.mcp.json`.

**▸ Integrasi VS Code Agent — 8:00**

- **🎯 Tujuan:** VS Code tersambung.
- **🎬 Rekam:** `.vscode/mcp.json` (key `servers`, `type: http/stdio`, token via `inputs`).
- **📁 File:** `clients/vscode.mcp.json`.
- **💬 Poin:** hati-hati beda key `servers` (VS Code) vs `mcpServers` (Cursor/Claude).

**▸ Integrasi Codex CLI (`mcp add --url`) — 7:00**

- **🎯 Tujuan:** Codex tersambung.
- **🎬 Rekam:** `codex mcp add task-management --url http://127.0.0.1:8000/mcp/task-management` → `codex mcp login`.
- **💬 Poin:** **wajib `--url`** — tanpa itu dianggap command (stdio), OAuth login gagal.

**▸ Cheatsheet `mcp add` tiap client — 9:00**

- **🎯 Tujuan:** merangkum perbedaan sintaks.
- **📊 Slide "stdio vs HTTP per Client":** tampilkan tabel — `claude --transport http`,
  `codex --url`, Cursor `url`, VS Code `type: http`. Tegaskan kapan pakai stdio vs HTTP.
- **🎬 Rekam:** buka tabel cheatsheet + contoh tiap client.
- **📁 File:** `WORKSPACE.md`.

## 14. Studi Kasus & Penutup

| Lesson | Durasi |
|--------|--------|
| Studi kasus automasi workflow (sprint, triage, standup) | 12:00 |
| Kolaborasi tim: invite & assign via AI | 8:00 |
| Tips membangun MCP untuk produksi | 9:00 |

**▸ Studi kasus automasi workflow — 12:00**

- **🎯 Tujuan:** menunjukkan nilai nyata.
- **🎬 Rekam:** demo alur — perencanaan sprint, triage & pindah task, bulk import (progress), standup.
- **📁 File:** `WORKSPACE.md`.
- **💬 Poin:** tunjukkan tool mana yang terpanggil untuk tiap perintah.

**▸ Kolaborasi tim: invite & assign via AI — 8:00**

- **🎯 Tujuan:** skenario multi-user.
- **🎬 Rekam:** "Undang budi@ ke board SPR, assign SPR-3 ke Budi"; via OAuth agar atas nama user.
- **💬 Poin:** assign hanya berhasil ke **anggota** board.

**▸ Tips membangun MCP untuk produksi — 9:00**

- **🎯 Tujuan:** penutup & bekal lanjutan.
- **📊 Slide "Penutup / Tips Produksi":** recap arc kelas — deskripsi tool yang baik,
  annotations keamanan, scoping per-user, HTTPS + OAuth + rate limit + logging.
- **🎬 Rekam:** slide penutup + arahkan ke dokumen repo & langkah berikutnya.
- **💬 Poin:** ucapkan selamat — murid kini bisa membangun MCP Server sendiri.

---

## Catatan Produksi

- **Checkpoint git per section** (tag/branch) agar murid bisa menyamakan progres.
- **Referensi kode & dokumen:** `MCP_SETUP.md`, `SECURITY.md`, `ADVANCED.md`, `WORKSPACE.md`, `DEPLOYMENT.md`, folder `clients/` & `deploy/`.
- **Verifikasi di layar:** setelah tiap fitur, jalankan `php artisan test` atau tunjukkan hasil di UI / MCP Inspector — agar penonton yakin kodenya jalan.
- **Slide:** deck ringkas (±10, 1 per section konsep) ada di file Figma; video koding = screencast tanpa slide.
