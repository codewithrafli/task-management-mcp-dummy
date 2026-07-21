<?php

use App\Mcp\Servers\TaskManagementServer;
use Laravel\Mcp\Facades\Mcp;

// Local (stdio) server — used by Claude Desktop via `php artisan mcp:start task-management`.
Mcp::local('task-management', TaskManagementServer::class);

// Web (HTTP) server — reachable at POST /mcp/task-management for remote clients & MCP Inspector.
// Protected with Sanctum token auth and a dedicated rate limiter (see AppServiceProvider).
Mcp::web('/mcp/task-management', TaskManagementServer::class)
    ->middleware(['auth:sanctum', 'throttle:mcp']);
