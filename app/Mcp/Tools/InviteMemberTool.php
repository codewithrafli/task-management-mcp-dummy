<?php

namespace App\Mcp\Tools;

use App\Mcp\Concerns\InteractsWithBoards;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Invite an existing user (by email) as a member of a board so tasks can be assigned to them. Only the board owner may invite.')]
class InviteMemberTool extends Tool
{
    use InteractsWithBoards;

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'board' => ['required'],
            'email' => ['required', 'email'],
        ]);

        $board = $this->resolveBoard($validated['board'], $request->user());

        if ($board === null) {
            return Response::error("Board \"{$validated['board']}\" not found.");
        }

        // Only the owner can manage members (when acting as an authenticated user).
        $actor = $request->user();
        if ($actor !== null && $board->user_id !== $actor->id) {
            return Response::error('Only the board owner can invite members.');
        }

        $user = User::where('email', $validated['email'])->first();

        if ($user === null) {
            return Response::error("No user found with email \"{$validated['email']}\". They must have an account first.");
        }

        if ($user->id === $board->user_id) {
            return Response::error("{$user->name} already owns this board.");
        }

        $board->members()->syncWithoutDetaching([$user->id]);

        return Response::text(sprintf('%s (%s) was added to board %s.', $user->name, $user->email, $board->code));
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
            'email' => $schema->string()
                ->description('Email of an existing user to add as a member.')
                ->required(),
        ];
    }
}
