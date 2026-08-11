<?php

namespace App\Console\Commands;

use App\Models\Policy;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class EmbedPolicies extends Command
{
    protected $signature = 'ai:embed-policies';

    protected $description = 'Generate embeddings for all policy documents (run once after seeding).';

    public function handle(): void
    {
        $policies = Policy::whereNull('embedding')->get();

        if ($policies->isEmpty()) {
            $this->info('All policies already have embeddings.');

            return;
        }

        $this->info("Embedding {$policies->count()} policies...");
        $bar = $this->output->createProgressBar($policies->count());

        foreach ($policies as $policy) {
            $policy->update([
                'embedding' => Str::of("{$policy->title}\n\n{$policy->content}")->toEmbeddings(),
            ]);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Done! Policies are now searchable by the Policy Advisor.');
    }
}
