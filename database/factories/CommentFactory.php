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
     * Define the model's default state: the fake attribute values used
     * whenever Comment::factory()->create()/make() is called without
     * overriding a given field.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Defaults to a brand-new issue (via a nested factory call) if no
            // issue is given. The seeder always overrides this via
            // ['issue_id' => $issue->id] so comments end up attached to the
            // correct, already-created issue.
            'issue_id' => Issue::factory(),
            'author_name' => fake()->name(), // A plausible random full name for the commenter.
            'body' => fake()->paragraph(), // A few sentences of placeholder comment text.
        ];
    }
}
