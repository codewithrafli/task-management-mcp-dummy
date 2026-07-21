<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

function initializePayload(): array
{
    return [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'initialize',
        'params' => [
            'protocolVersion' => '2025-06-18',
            'capabilities' => (object) [],
            'clientInfo' => ['name' => 'test', 'version' => '1'],
        ],
    ];
}

it('rejects unauthenticated requests to the MCP HTTP endpoint', function () {
    $this->postJson('/mcp/task-management', initializePayload())
        ->assertUnauthorized();
});

it('accepts requests authenticated with a Sanctum token', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->withHeaders(['Accept' => 'application/json, text/event-stream'])
        ->postJson('/mcp/task-management', initializePayload())
        ->assertOk();
});
