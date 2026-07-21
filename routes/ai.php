<?php

use App\Mcp\Servers\TaskManagementServer;
use Laravel\Mcp\Facades\Mcp;

// Local (stdio) server — used by Claude Desktop via `php artisan mcp:start task-management`.
Mcp::local('task-management', TaskManagementServer::class);

// Web (HTTP) server — reachable at POST /mcp/task-management for remote clients & MCP Inspector.
Mcp::web('/mcp/task-management', TaskManagementServer::class);
