<?php

namespace Database\Factories;

use App\Models\Issue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Defaults to a new issue; override via for() or explicit issue_id in the seeder.
            'issue_id' => Issue::factory(),
            'author_name' => fake()->name(),
            'body' => fake()->paragraph(),
        ];
    }
}
