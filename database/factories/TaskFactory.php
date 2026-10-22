<?php

namespace Database\Factories;

use App\Enums\Stage;
use App\Models\Task;
use App\Models\Ticket;
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
            'ticket_id' => Ticket::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'stage' => Stage::Todo,
            'position' => 0,
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes): array => [
            'stage' => Stage::InProgress,
        ]);
    }

    public function done(): static
    {
        return $this->state(fn (array $attributes): array => [
            'stage' => Stage::Done,
        ]);
    }
}
