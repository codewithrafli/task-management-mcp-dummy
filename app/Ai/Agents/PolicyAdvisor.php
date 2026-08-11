<?php

namespace App\Ai\Agents;

use App\Models\Policy;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Laravel\Ai\Tools\Request;

#[Provider(Lab::Gemini)]
class PolicyAdvisor implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    public function instructions(): string
    {
        return <<<'PROMPT'
        You are a policy and SOP advisor for this team's task management system.
        You answer questions strictly based on the team's official documents and policies.

        Rules:
        - Only answer based on the documents retrieved — do NOT use general knowledge.
        - If you cannot find the answer in the documents, say so clearly.
        - Always cite which policy/document your answer comes from.
        - Respond in the same language the user uses (Indonesian or English).
        PROMPT;
    }

    public function tools(): iterable
    {
        return [
            new class implements Tool
            {
                public function description(): string
                {
                    return 'Search team policies, SOPs, and guidelines to answer questions.';
                }

                public function handle(Request $request): string
                {
                    $queryEmbedding = Str::of($request['query'])->toEmbeddings();

                    $results = Policy::whereNotNull('embedding')
                        ->get()
                        ->map(fn (Policy $policy) => [
                            'policy' => $policy,
                            'score' => self::cosineSimilarity($queryEmbedding, $policy->embedding),
                        ])
                        ->sortByDesc('score')
                        ->take(3)
                        ->filter(fn ($r) => $r['score'] > 0.5);

                    if ($results->isEmpty()) {
                        return 'No relevant policies found.';
                    }

                    return $results->map(fn ($r) => sprintf(
                        "[%s — %s]\n%s",
                        $r['policy']->title,
                        $r['policy']->source,
                        $r['policy']->content,
                    ))->implode("\n\n");
                }

                public function schema(JsonSchema $schema): array
                {
                    return [
                        'query' => $schema->string()
                            ->description('The question or topic to search policies for.')
                            ->required(),
                    ];
                }

                private static function cosineSimilarity(array $a, array $b): float
                {
                    $dot = array_sum(array_map(fn ($x, $y) => $x * $y, $a, $b));
                    $normA = sqrt(array_sum(array_map(fn ($x) => $x ** 2, $a)));
                    $normB = sqrt(array_sum(array_map(fn ($x) => $x ** 2, $b)));

                    return ($normA && $normB) ? $dot / ($normA * $normB) : 0.0;
                }
            },
        ];
    }
}
