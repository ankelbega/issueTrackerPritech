<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state: the fake attribute values used
     * whenever Project::factory()->create()/make() is called without
     * overriding a given field.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Pick a random start date within the last 6 months, so seeded
        // projects look like they've been ongoing for a realistic amount of time.
        $startDate = fake()->dateTimeBetween('-6 months', 'now');

        // The deadline is always 1-3 months after the start date (never before
        // it), so seeded data never violates the "deadline >= start_date" rule
        // enforced by StoreProjectRequest/UpdateProjectRequest.
        $deadline = (clone $startDate)->modify('+'.fake()->numberBetween(1, 3).' months');

        return [
            'name' => fake()->catchPhrase(), // Faker's catchPhrase() reads like a plausible product/project name.
            'description' => fake()->paragraph(), // A few sentences of placeholder text.
            'start_date' => $startDate,
            'deadline' => $deadline,
            // Note: user_id is intentionally not set here — the seeder assigns
            // ownership explicitly via ->for($admin) / ->for($user) so each
            // project ends up owned by a specific, known account.
        ];
    }
}
