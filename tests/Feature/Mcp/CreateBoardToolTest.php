<?php

use App\Mcp\Servers\TaskManagementServer;
use App\Mcp\Tools\CreateBoardTool;

it('creates a board via the MCP tool', function () {
    $response = TaskManagementServer::tool(CreateBoardTool::class, [
        'name' => 'Rilis v2',
        'description' => 'From MCP',
    ]);

    $response->assertOk()->assertSee('Rilis v2');
    $this->assertDatabaseHas('boards', ['name' => 'Rilis v2']);
});

it('rejects a board without a name', function () {
    TaskManagementServer::tool(CreateBoardTool::class, [
        'description' => 'no name',
    ])->assertHasErrors();
});
