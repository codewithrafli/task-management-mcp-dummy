<?php

namespace App\Mcp\Tools;

use App\Mcp\Concerns\InteractsWithBoards;
use App\Services\BoardService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Rename a board or change its description. Reference the board by its code (e.g. "SPR") or numeric id.')]
class RenameBoardTool extends Tool
{
    use InteractsWithBoards;

    public function __construct(private readonly BoardService $boards) {}

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'board' => ['required'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
        ]);

        $board = $this->resolveBoard($validated['board'], $request->user());

        if ($board === null) {
            return Response::error("Board \"{$validated['board']}\" not found.");
        }

        $changes = collect($validated)->except('board')->all();

        if ($changes === []) {
            return Response::error('Nothing to update. Provide a name or description.');
        }

        $this->boards->update($board, $changes);

        return Response::text(sprintf('Board %s updated (%s).', $board->code, implode(', ', array_keys($changes))));
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
            'name' => $schema->string()->description('New board name.'),
            'description' => $schema->string()->description('New board description.'),
        ];
    }
}
