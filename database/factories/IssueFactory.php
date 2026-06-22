<?php

namespace Database\Factories;

use App\Models\Issue;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Issue>
 */
class IssueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Defaults to a new project; override via for() or explicit project_id in the seeder.
            'project_id' => Project::factory(),
            'title' => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement([
                Issue::STATUS_OPEN,
                Issue::STATUS_IN_PROGRESS,
                Issue::STATUS_CLOSED,
            ]),
            'priority' => fake()->randomElement([
                Issue::PRIORITY_LOW,
                Issue::PRIORITY_MEDIUM,
                Issue::PRIORITY_HIGH,
            ]),
            // 70% chance of having a due date, otherwise null.
            'due_date' => fake()->boolean(70) ? fake()->dateTimeBetween('now', '+2 months') : null,
        ];
    }
}
