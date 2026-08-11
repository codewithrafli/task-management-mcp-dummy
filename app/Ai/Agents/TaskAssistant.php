<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Laravel\Mcp\Client;

#[Provider(Lab::Gemini)]
class TaskAssistant implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    public function instructions(): string
    {
        return <<<'PROMPT'
        You are a helpful task management assistant for a Trello-like app called TaskFlow.
        You help users manage their boards and tasks by taking actions on their behalf.

        You have access to tools to:
        - List boards and tasks
        - Create boards and tasks
        - Update task status (todo, in_progress, done)
        - Move tasks between boards
        - Search for tasks
        - Delete tasks

        Guidelines:
        - Always confirm what you did after taking an action.
        - When listing tasks, format them clearly (code, title, status, priority).
        - If asked to do something in bulk, do it step by step.
        - Respond in the same language the user uses (Indonesian or English).
        - Be concise and actionable.
        PROMPT;
    }

    public function tools(): iterable
    {
        return [
            ...Client::local('php', [base_path('artisan'), 'mcp:start', 'task-management'])->tools(),
        ];
    }
}
