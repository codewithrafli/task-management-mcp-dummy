<?php

namespace App\Mcp\Tools;

use App\Http\Requests\StoreBoardRequest;
use App\Models\Board;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Create a new board to organise tasks.')]
class CreateBoardTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate((new StoreBoardRequest)->rules());

        // Sebelum OAuth, tak ada user yang login lewat MCP — jadi board diberikan ke
        // user pertama (Test User) supaya muncul di UI demo.
        $board = Board::create([
            ...$validated,
            'user_id' => User::query()->value('id'),
        ]);

        return Response::text(sprintf('Board %s "%s" created.', $board->code, $board->name));
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
