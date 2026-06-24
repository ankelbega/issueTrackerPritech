<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `tags` table — reusable labels (e.g. "Bug", "Frontend") that
 * can be attached to any number of issues via the `issue_tag` pivot table.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key.
            $table->string('name')->unique(); // Tag label; unique so the same tag can't be created twice.
            $table->string('color')->nullable(); // Hex color (e.g. "#ef4444") used to render the tag's pill; nullable for a neutral default.
            $table->timestamps(); // created_at / updated_at columns.
        });
    }

    /**
     * Reverse the migrations: drop the `tags` table.
     */
    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
