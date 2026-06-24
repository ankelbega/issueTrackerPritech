<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `issue_user` pivot table, implementing the many-to-many
 * "assignment" relationship between issues and users (one issue can be
 * assigned to many users, one user can be assigned to many issues). No
 * model exists for this table — Eloquent manages it via Issue::users() /
 * User::issues().
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('issue_user', function (Blueprint $table) {
            // Foreign key to issues.id; deleting an issue removes its assignments too.
            $table->foreignId('issue_id')->constrained()->cascadeOnDelete();
            // Foreign key to users.id; deleting a user removes their assignments too.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Composite primary key: prevents assigning the same user to the same
            // issue twice, and means there's no need for a separate id column.
            $table->primary(['issue_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations: drop the `issue_user` pivot table.
     */
    public function down(): void
    {
        Schema::dropIfExists('issue_user');
    }
};
