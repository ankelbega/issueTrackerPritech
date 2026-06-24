<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds project ownership: links each project to the user who created it,
 * which the ProjectPolicy uses to decide who is allowed to update/delete it.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Nullable: existing projects (and any created without an authenticated
            // user, e.g. via tinker/seeders) aren't forced to have an owner.
            // nullOnDelete() means deleting a user sets their projects' user_id to
            // null instead of deleting the projects themselves.
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations: drop the user_id column and its foreign key constraint.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
