<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Populates the database with realistic demo data so the app can be
 * explored immediately after a fresh `php artisan migrate --seed` without
 * having to manually create projects/issues/tags by hand.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        // Create two known users so we can log in and test the app immediately,
        // with fixed emails/passwords instead of random Faker ones.
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            // The User model's 'password' cast is 'hashed', so this plain-text
            // string is automatically hashed before being saved — never stored as-is.
            'password' => 'password',
        ]);

        $user = User::factory()->create([
            'name' => 'User',
            'email' => 'user@test.com',
            'password' => 'password',
        ]);

        // Collected together so both users are available later when randomly
        // assigning issues to assignees, regardless of which project owns them.
        $users = collect([$admin, $user]);

        // Create the fixed set of 8 tags (Bug, Feature, Urgent, etc.) from TagFactory::TAGS.
        $tags = Tag::factory()->count(8)->create();

        // Create 5 projects owned by specific users: the first 3 belong to admin,
        // the last 2 belong to user@test.com, so ownership/policy checks have
        // realistic data to work against right after seeding.
        // for($admin) sets each new project's user_id to $admin->id automatically.
        $adminProjects = Project::factory()->count(3)->for($admin)->create();
        $userProjects = Project::factory()->count(2)->for($user)->create();

        // Combine both owners' projects into one collection so the same
        // issue/comment/assignment generation logic below runs for all 5.
        $adminProjects->concat($userProjects)
            ->each(function (Project $project) use ($tags, $users) {
                // Each project gets between 4 and 8 issues, tied to this project via for().
                $issueCount = fake()->numberBetween(4, 8);

                Issue::factory()
                    ->count($issueCount)
                    ->for($project) // Sets each new issue's project_id to this $project's id.
                    ->create()
                    ->each(function (Issue $issue) use ($tags, $users) {
                        // Attach 1-3 random tags to the issue (issue_tag pivot).
                        // ->random(n) picks n distinct tags from the full set of 8;
                        // ->pluck('id') reduces them to just the ids attach() needs.
                        $issue->tags()->attach(
                            $tags->random(fake()->numberBetween(1, 3))->pluck('id')
                        );

                        // Create 2-4 comments for the issue, explicitly tying each
                        // generated comment to this issue's id.
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
