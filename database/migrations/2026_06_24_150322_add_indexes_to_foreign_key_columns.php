<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds secondary indexes on foreign key columns that only had the FK
 * constraint itself (no actual index). foreignId()->constrained() does not
 * automatically create a secondary index on every driver — confirmed via
 * sqlite_master that issues.project_id, comments.issue_id, and
 * projects.user_id had zero indexes, meaning every lookup by these columns
 * (e.g. $project->issues(), $issue->comments(), $user->projects()) was a
 * full table scan. Also adds the reverse-lookup indexes on the pivot
 * tables' second column (tag_id, user_id), since their composite primary
 * key only covers lookups starting from the first column (issue_id).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->index('project_id');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->index('issue_id');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->index('user_id');
        });

        Schema::table('issue_tag', function (Blueprint $table) {
            // Speeds up Tag::issues() lookups (WHERE tag_id = ?), which the
            // composite primary key (issue_id, tag_id) doesn't cover on its own.
            $table->index('tag_id');
        });

        Schema::table('issue_user', function (Blueprint $table) {
            // Speeds up User::issues() lookups (WHERE user_id = ?), same
            // reasoning as the tag_id index above.
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations: drop all five indexes added above.
     */
    public function down(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->dropIndex(['project_id']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['issue_id']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('issue_tag', function (Blueprint $table) {
            $table->dropIndex(['tag_id']);
        });

        Schema::table('issue_user', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });
    }
};
