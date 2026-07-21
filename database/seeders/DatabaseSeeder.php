<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $test = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'is_admin' => true,
        ]);

        $budi = User::factory()->create(['name' => 'Budi', 'email' => 'budi@example.com']);
        $ani = User::factory()->create(['name' => 'Ani', 'email' => 'ani@example.com']);

        $boards = Board::factory(3)
            ->for($test)
            ->has(Task::factory()->count(6))
            ->create();

        // Budi & Ani are members of every board, so they can be assigned tasks.
        $boards->each(fn (Board $board) => $board->members()->sync([$budi->id, $ani->id]));

        // Assign a spread of tasks so assignee-based demos have data.
        Task::query()->inRandomOrder()->limit(9)->get()
            ->each(fn (Task $task) => $task->update([
                'assignee_id' => fake()->randomElement([$test->id, $budi->id, $ani->id]),
            ]));
    }
}
