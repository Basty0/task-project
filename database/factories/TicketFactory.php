<?php

namespace Database\Factories;

use App\Enums\Stage;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
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
