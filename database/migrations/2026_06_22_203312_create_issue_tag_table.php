<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `issue_tag` pivot table, implementing the many-to-many
 * relationship between issues and tags (one issue can have many tags, one
 * tag can be attached to many issues). No model exists for this table —
 * Eloquent manages it automatically via Issue::tags() / Tag::issues().
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('issue_tag', function (Blueprint $table) {
            // Foreign key to issues.id; deleting an issue removes its tag links too.
            $table->foreignId('issue_id')->constrained()->cascadeOnDelete();
            // Foreign key to tags.id; deleting a tag removes its issue links too.
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            // Composite primary key: prevents the same tag being attached to the
            // same issue twice, and means there's no need for a separate id column.
            $table->primary(['issue_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations: drop the `issue_tag` pivot table.
     */
    public function down(): void
    {
        Schema::dropIfExists('issue_tag');
    }
};
