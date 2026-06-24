<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `issues` table — the core unit of work in the Issue Tracker.
 * Every issue belongs to a project, can carry tags, comments, and assigned
 * users, and tracks its own status/priority lifecycle.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('issues', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key.
            // Foreign key to projects.id. cascadeOnDelete() means deleting a
            // project automatically deletes all of its issues (and, by the
            // same cascade rule on other tables, their comments/pivots too).
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title'); // Short summary shown in lists/tables.
            $table->text('description')->nullable(); // Optional longer details; not every issue needs one.
            // Workflow state. Restricted to these three values at the database
            // level as a safety net, matching the Issue model's STATUS_* constants.
            $table->enum('status', ['open', 'in_progress', 'closed'])->default('open');
            // Urgency level. Defaults to 'medium' so new issues aren't accidentally
            // marked low/high priority unless explicitly chosen.
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->date('due_date')->nullable(); // Optional target resolution date.
            $table->timestamps(); // created_at / updated_at columns.
        });
    }

    /**
     * Reverse the migrations: drop the `issues` table.
     */
    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
