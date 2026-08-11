<?php

namespace App\Ai\Agents;

use App\Models\Policy;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Laravel\Ai\Tools\SimilaritySearch;

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
            SimilaritySearch::usingModel(Policy::class, 'embedding')
                ->withDescription('Search team policies, SOPs, and guidelines to answer questions.'),
        ];
    }
}
