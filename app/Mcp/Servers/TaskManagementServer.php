<?php

namespace App\Mcp\Servers;

use App\Mcp\Prompts\PlanBoardPrompt;
use App\Mcp\Prompts\StandupPrompt;
use App\Mcp\Resources\BoardsResource;
use App\Mcp\Resources\BoardTasksResource;
use App\Mcp\Resources\TasksResource;
use App\Mcp\Tools\AssignTaskTool;
use App\Mcp\Tools\BulkCreateTasksTool;
use App\Mcp\Tools\CreateBoardTool;
use App\Mcp\Tools\CreateTaskTool;
use App\Mcp\Tools\DeleteTaskTool;
use App\Mcp\Tools\ListTasksTool;
use App\Mcp\Tools\MoveTaskTool;
use App\Mcp\Tools\MyTasksTool;
use App\Mcp\Tools\SearchTaskTool;
use App\Mcp\Tools\UpdateTaskStatusTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Task Management Server')]
#[Version('0.1.0')]
#[Instructions(
    'This server manages a Trello-like task board. '
    .'Use the boards and tasks resources to read the current state. '
    .'Use the tools to create boards, create tasks, update a task status, '
    .'move a task between boards, search tasks, assign tasks to users, '
    .'and list the authenticated user\'s own tasks (my-tasks-tool). '
    .'Tasks may have an assignee and a due date; list-tasks-tool can filter by '
    .'assignee_id or overdue.'
)]
class TaskManagementServer extends Server
{
    protected array $tools = [
        CreateBoardTool::class,
        CreateTaskTool::class,
        UpdateTaskStatusTool::class,
        MoveTaskTool::class,
        SearchTaskTool::class,
        DeleteTaskTool::class,
        ListTasksTool::class,
        BulkCreateTasksTool::class,
        AssignTaskTool::class,
        MyTasksTool::class,
    ];

    protected array $resources = [
        BoardsResource::class,
        TasksResource::class,
        BoardTasksResource::class,
    ];

    protected array $prompts = [
        PlanBoardPrompt::class,
        StandupPrompt::class,
    ];
}
