<?php

use App\Mcp\Servers\TaskManagementServer;
use Laravel\Mcp\Facades\Mcp;

// Local (stdio) server — used by Claude Desktop via `php artisan mcp:start task-management`.
Mcp::local('task-management', TaskManagementServer::class);

// OAuth 2.1 discovery + dynamic client registration for MCP clients (Passport-backed).
// Exposes /.well-known/oauth-authorization-server, /.well-known/oauth-protected-resource
// and POST /oauth/register.
Mcp::oauthRoutes();

// Web (HTTP) server — reachable at POST /mcp/task-management for remote clients & MCP Inspector.
// Protected with OAuth 2.1 access tokens (Passport 'api' guard) and a dedicated rate limiter.
Mcp::web('/mcp/task-management', TaskManagementServer::class)
    ->middleware(['auth:api', 'throttle:mcp']);
