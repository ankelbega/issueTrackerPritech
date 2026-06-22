<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create two known users so we can log in and test the app immediately.
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $user = User::factory()->create([
            'name' => 'User',
            'email' => 'user@test.com',
            'password' => 'password',
        ]);

        $users = collect([$admin, $user]);

        // Create the fixed set of 8 tags (Bug, Feature, Urgent, etc.) from TagFactory::TAGS.
        $tags = Tag::factory()->count(8)->create();

        // Create 5 projects, each with a handful of issues, comments and assignments.
        Project::factory()
            ->count(5)
            ->create()
            ->each(function (Project $project) use ($tags, $users) {
                // Each project gets between 4 and 8 issues, tied to this project via for().
                $issueCount = fake()->numberBetween(4, 8);

                Issue::factory()
                    ->count($issueCount)
                    ->for($project)
                    ->create()
                    ->each(function (Issue $issue) use ($tags, $users) {
                        // Attach 1-3 random tags to the issue (issue_tag pivot).
                        $issue->tags()->attach(
                            $tags->random(fake()->numberBetween(1, 3))->pluck('id')
                        );

                        // Create 2-4 comments for the issue.
                        Comment::factory()
                            ->count(fake()->numberBetween(2, 4))
                            ->create(['issue_id' => $issue->id]);

                        // Assign 1-2 random users to the issue (issue_user pivot).
                        $issue->users()->attach(
                            $users->random(fake()->numberBetween(1, 2))->pluck('id')
                        );
                    });
            });
    }
}
