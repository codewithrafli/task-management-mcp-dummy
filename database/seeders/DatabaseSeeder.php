<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ensure a Passport personal access client exists so `php artisan mcp:token`
        // works right after `migrate:fresh --seed`.
        if (! Client::where('grant_types', 'like', '%personal_access%')->exists()) {
            app(ClientRepository::class)->createPersonalAccessGrantClient('MCP Personal Access Client');
        }

        $test = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'is_admin' => true,
        ]);

        $budi = User::factory()->create(['name' => 'Budi', 'email' => 'budi@example.com']);
        $ani = User::factory()->create(['name' => 'Ani', 'email' => 'ani@example.com']);

        Board::factory(3)
            ->has(Task::factory()->count(6))
            ->create();

        // Assign a spread of tasks so assignee-based demos have data.
        Task::query()->inRandomOrder()->limit(9)->get()
            ->each(fn (Task $task) => $task->update([
                'assignee_id' => fake()->randomElement([$test->id, $budi->id, $ani->id]),
            ]));
    }
}
