# Advanced MCP Features (Module 8)

Fitur MCP lanjutan yang diimplementasikan di server ini.

---

## 1. Pagination & Filtering — `ListTasksTool`

Tool `list-tasks-tool` mengembalikan **satu halaman** task plus metadata paginasi,
dengan filter opsional.

Argumen: `board_id`, `status`, `priority`, `page`, `per_page` (default 10, max 100).

```json
{
  "data": [ { "id": 1, "title": "...", "status": "todo", "priority": "high" } ],
  "pagination": {
    "current_page": 1, "per_page": 10, "total": 15,
    "last_page": 2, "has_more": true
  }
}
```

Prinsip: **jangan kembalikan ribuan baris** ke AI. Batasi `per_page`, sertakan
`has_more`/`last_page` agar AI bisa meminta halaman berikutnya.

## 2. Dynamic Resource (URI Template) — `BoardTasksResource`

Resource beralamat dinamis memakai **URI template**:

```
board://{boardId}/tasks
```

```php
class BoardTasksResource extends Resource implements HasUriTemplate
{
    public function uriTemplate(): UriTemplate
    {
        return new UriTemplate('board://{boardId}/tasks');
    }

    public function handle(Request $request): Response
    {
        $boardId = (int) $request->get('boardId'); // diambil dari URI
        // ...
    }
}
```

Variabel `{boardId}` otomatis di-*match* dari URI dan tersedia via `$request->get()`.
Berguna untuk mengekspos data ber-parameter tanpa membuat satu resource per board.

## 3. Prompt Template — `StandupPrompt`

Prompt dengan **beberapa argumen** (`board`, `audience`) yang menyusun instruksi
kontekstual, bahkan menyisipkan data nyata (hitungan task per status) ke dalam prompt.

```php
public function arguments(): array
{
    return [
        new Argument('board', 'The board name to summarise.', required: true),
        new Argument('audience', 'Who the update is for.', required: false),
    ];
}
```

Prompt = template yang dapat dipilih user di client (mis. slash command di Claude),
lalu diisi argumen.

## 4. Long-Running Task + Progress Notification — `BulkCreateTasksTool`

Untuk operasi panjang (membuat banyak task sekaligus), `handle()` mengembalikan
**`Generator`** yang `yield` notifikasi progress lalu hasil akhir:

```php
public function handle(Request $request): Generator
{
    $progressToken = $request->meta()['progressToken'] ?? null;

    foreach ($titles as $i => $title) {
        $this->tasks->create([...]);

        if ($progressToken !== null) {
            yield Response::notification('notifications/progress', [
                'progressToken' => $progressToken,
                'progress' => $i + 1,
                'total' => $total,
            ]);
        }
    }

    yield Response::text("Created {$total} task(s).");
}
```

Catatan:
- Kirim progress **hanya jika** client menyertakan `progressToken` di `_meta`
  (sesuai spesifikasi MCP).
- Notifikasi memakai method `notifications/progress` dengan `progress` & `total`.
- Response terakhir yang di-`yield` adalah hasil tool.

Verifikasi via stdio:

```bash
printf '%s\n' \
'{"jsonrpc":"2.0","id":1,"method":"initialize","params":{...}}' \
'{"jsonrpc":"2.0","method":"notifications/initialized"}' \
'{"jsonrpc":"2.0","id":2,"method":"tools/call","params":{"name":"bulk-create-tasks-tool","arguments":{"board_id":1,"titles":["A","B"]},"_meta":{"progressToken":"p1"}}}' \
| php artisan mcp:start task-management
# -> memancarkan 2x notifications/progress, lalu hasil akhir
```

---

Test untuk keempat fitur ada di `tests/Feature/Mcp/AdvancedFeaturesTest.php`.
