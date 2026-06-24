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
     * Define the model's default state: the fake attribute values used
     * whenever Issue::factory()->create()/make() is called without
     * overriding a given field.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Defaults to a brand-new project (via a nested factory call) if no
            // project is given. The seeder always overrides this via for($project)
            // so issues end up attached to the correct, already-created project
            // rather than spawning extra random projects.
            'project_id' => Project::factory(),
            'title' => fake()->sentence(6), // A short, plausible-looking 6-word title.
            'description' => fake()->paragraph(), // A few sentences of placeholder text.
            // Picks one of the three valid statuses at random, using the Issue
            // model's own constants so this stays in sync if they ever change.
            'status' => fake()->randomElement([
                Issue::STATUS_OPEN,
                Issue::STATUS_IN_PROGRESS,
                Issue::STATUS_CLOSED,
            ]),
            // Picks one of the three valid priorities at random, same reasoning as status.
            'priority' => fake()->randomElement([
                Issue::PRIORITY_LOW,
                Issue::PRIORITY_MEDIUM,
                Issue::PRIORITY_HIGH,
            ]),
            // 70% chance of having a due date, otherwise null — mirrors real
            // usage where not every issue gets an explicit deadline.
            'due_date' => fake()->boolean(70) ? fake()->dateTimeBetween('now', '+2 months') : null,
        ];
    }
}
