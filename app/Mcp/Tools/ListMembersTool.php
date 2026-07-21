<?php

namespace App\Mcp\Tools;

use App\Mcp\Concerns\InteractsWithBoards;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('List the members (owner + collaborators) of a board. These are the users a task on that board can be assigned to.')]
#[IsReadOnly]
class ListMembersTool extends Tool
{
    use InteractsWithBoards;

    public function handle(Request $request): Response
    {
        $validated = $request->validate(['board' => ['required']]);

        $board = $this->resolveBoard($validated['board'], $request->user());

        if ($board === null) {
            return Response::error("Board \"{$validated['board']}\" not found.");
        }

        $members = $board->team()->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'role' => $u->id === $board->user_id ? 'owner' : 'member',
        ]);

        return Response::json([
            'board' => ['code' => $board->code, 'name' => $board->name],
            'members' => $members,
        ]);
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'board' => $schema->string()
                ->description('The board code (e.g. "SPR") or numeric id.')
                ->required(),
        ];
    }
}
