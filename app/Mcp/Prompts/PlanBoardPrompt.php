<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Description('Generate a set of starter tasks for a given project goal, ready to be created on a board.')]
class PlanBoardPrompt extends Prompt
{
    public function handle(Request $request): Response
    {
        $goal = $request->get('goal', 'a new project');

        return Response::text(
            "You are a project planner. Break down the goal \"{$goal}\" into 5-8 concrete, "
            ."actionable tasks. For each task suggest a priority (low, medium, high). "
            ."Then use the create-task tool to add each task to the appropriate board."
        );
    }

    /**
     * @return array<int, Argument>
     */
    public function arguments(): array
    {
        return [
            new Argument(
                name: 'goal',
                description: 'The project goal or objective to plan tasks for.',
                required: true,
            ),
        ];
    }
}
