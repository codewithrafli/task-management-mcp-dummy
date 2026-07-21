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
use App\Mcp\Tools\InviteMemberTool;
use App\Mcp\Tools\ListMembersTool;
use App\Mcp\Tools\ListTasksTool;
use App\Mcp\Tools\MoveTaskTool;
use App\Mcp\Tools\MyTasksTool;
use App\Mcp\Tools\RenameBoardTool;
use App\Mcp\Tools\SearchTaskTool;
use App\Mcp\Tools\UpdateTaskStatusTool;
use App\Mcp\Tools\UpdateTaskTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Task Management Server')]
#[Version('0.1.0')]
#[Instructions(
    'This server manages a Trello-like task board. '
    .'When authenticated, you only see and can modify boards the user owns or is a member of. '
    .'Read state with the boards and tasks resources. Tasks are referenced by their code '
    .'(e.g. "SPR-1") or numeric id. Use the tools to create/rename boards, create tasks, '
    .'edit a task (update-task-tool) or just its status, move tasks between boards, search '
    .'and list tasks, assign tasks, list the current user\'s tasks (my-tasks-tool), and manage '
    .'board members (list-members-tool, invite-member-tool). Tasks may only be assigned to '
    .'board members; list-tasks-tool can filter by assignee_id or overdue.'
)]
class TaskManagementServer extends Server
{
    protected array $tools = [
        CreateBoardTool::class,
        RenameBoardTool::class,
        CreateTaskTool::class,
        UpdateTaskTool::class,
        UpdateTaskStatusTool::class,
        MoveTaskTool::class,
        SearchTaskTool::class,
        DeleteTaskTool::class,
        ListTasksTool::class,
        BulkCreateTasksTool::class,
        AssignTaskTool::class,
        MyTasksTool::class,
        ListMembersTool::class,
        InviteMemberTool::class,
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
