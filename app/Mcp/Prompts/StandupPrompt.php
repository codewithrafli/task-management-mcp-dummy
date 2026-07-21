<?php

namespace App\Mcp\Prompts;

use App\Models\Board;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Description('Produce a daily standup summary prompt for a board: what is done, in progress, and blocked.')]
class StandupPrompt extends Prompt
{
    public function handle(Request $request): Response
    {
        $boardName = $request->get('board', 'the current board');
        $audience = $request->get('audience', 'the team');

        $context = '';
        if ($board = Board::where('name', $boardName)->first()) {
            $counts = $board->tasks()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $context = ' Current counts — '.$counts->map(
                fn ($total, $status) => "{$status}: {$total}"
            )->implode(', ').'.';
        }

        return Response::text(
            "Write a concise daily standup update for {$audience} about the board "
            ."\"{$boardName}\".{$context} Group the update into three sections: "
            ."Done, In Progress, and Blocked/Next. Keep it under 120 words and use bullet points."
        );
    }

    /**
     * @return array<int, Argument>
     */
    public function arguments(): array
    {
        return [
            new Argument(
                name: 'board',
                description: 'The board name to summarise.',
                required: true,
            ),
            new Argument(
                name: 'audience',
                description: 'Who the update is for (e.g. "the team", "stakeholders").',
                required: false,
            ),
        ];
    }
}
