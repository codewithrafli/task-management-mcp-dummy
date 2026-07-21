<?php

namespace App\Mcp\Tools;

use App\Services\BoardService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Create a new board to organise tasks.')]
class CreateBoardTool extends Tool
{
    public function __construct(private readonly BoardService $boards) {}

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $board = $this->boards->create($validated);

        return Response::text(sprintf('Board #%d "%s" created.', $board->id, $board->name));
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()
                ->description('The board name.')
                ->required(),
            'description' => $schema->string()
                ->description('Optional board description.'),
        ];
    }
}
