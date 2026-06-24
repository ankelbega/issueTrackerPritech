<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `comments` table — free-text notes left on an issue. Comments
 * use a plain `author_name` string rather than a user_id foreign key, since
 * the comment form doesn't require the commenter to be a registered user.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key.
            // Foreign key to issues.id; deleting an issue deletes its comments too.
            $table->foreignId('issue_id')->constrained()->cascadeOnDelete();
            $table->string('author_name'); // Free-text name of whoever left the comment.
            $table->text('body'); // The comment's content.
            $table->timestamps(); // created_at / updated_at columns; created_at is shown in the UI.
        });
    }

    /**
     * Reverse the migrations: drop the `comments` table.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
