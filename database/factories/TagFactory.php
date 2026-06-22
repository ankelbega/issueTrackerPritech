<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
 */
class TagFactory extends Factory
{
    /**
     * Fixed set of tag names mapped to their hex color, used for demo seeding
     * instead of random Faker data so the tags stay consistent and recognizable.
     */
    public const TAGS = [
        'Bug' => '#ef4444',
        'Feature' => '#3b82f6',
        'Urgent' => '#f97316',
        'Frontend' => '#8b5cf6',
        'Backend' => '#10b981',
        'Design' => '#ec4899',
        'Testing' => '#f59e0b',
        'Documentation' => '#6b7280',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Pick a name from the fixed set without repeating one already used.
        $name = fake()->unique()->randomElement(array_keys(self::TAGS));

        return [
            'name' => $name,
            'color' => self::TAGS[$name],
        ];
    }
}
