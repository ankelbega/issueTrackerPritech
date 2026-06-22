<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Pick a random start date within the last 6 months.
        $startDate = fake()->dateTimeBetween('-6 months', 'now');

        // The deadline is always 1-3 months after the start date.
        $deadline = (clone $startDate)->modify('+'.fake()->numberBetween(1, 3).' months');

        return [
            'name' => fake()->catchPhrase(),
            'description' => fake()->paragraph(),
            'start_date' => $startDate,
            'deadline' => $deadline,
        ];
    }
}
