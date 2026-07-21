<?php

use App\Mcp\Servers\TaskManagementServer;
use App\Mcp\Tools\DeleteTaskTool;
use App\Models\Task;
use App\Models\User;

it('lets an admin delete a task', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $task = Task::factory()->create();

    TaskManagementServer::actingAs($admin)
        ->tool(DeleteTaskTool::class, ['task_id' => $task->id])
        ->assertOk()
        ->assertSee('deleted');

    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
});

it('forbids a non-admin from deleting a task', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $task = Task::factory()->create();

    TaskManagementServer::actingAs($user)
        ->tool(DeleteTaskTool::class, ['task_id' => $task->id])
        ->assertHasErrors();

    $this->assertDatabaseHas('tasks', ['id' => $task->id]);
});
