<?php

use App\Mcp\Servers\TaskManagementServer;
use Laravel\Mcp\Facades\Mcp;

// Local (stdio) server — dipakai Claude Desktop via `php artisan mcp:start task-management`.
Mcp::local('task-management', TaskManagementServer::class);

// Web (HTTP) server — POST /mcp/task-management.
// Catatan: pada tahap ini BELUM diproteksi OAuth. Keamanan (OAuth 2.1, rate limit,
// scoping) ditambahkan di modul Authentication & Security.
Mcp::web('/mcp/task-management', TaskManagementServer::class);
