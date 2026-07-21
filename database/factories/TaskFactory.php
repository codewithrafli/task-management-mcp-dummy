<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Board;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'board_id' => Board::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(TaskStatus::values()),
            'priority' => fake()->randomElement(TaskPriority::values()),
            'due_date' => fake()->optional()->dateTimeBetween('-1 week', '+3 weeks')?->format('Y-m-d'),
            'position' => fake()->numberBetween(1, 20),
        ];
    }
}
