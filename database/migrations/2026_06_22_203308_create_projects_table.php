<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `projects` table — the top-level container in the Issue
 * Tracker. Every issue belongs to exactly one project, so this table must
 * exist before the `issues` table is created.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key, referenced by issues.project_id.
            $table->string('name'); // Project title shown throughout the UI (e.g. card headings, breadcrumbs).
            $table->text('description')->nullable(); // Optional longer description; nullable since not every project needs one.
            $table->timestamps(); // created_at / updated_at columns.
        });
    }

    /**
     * Reverse the migrations: drop the `projects` table.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
