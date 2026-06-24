<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds optional scheduling fields to the `projects` table. Kept as a
 * separate migration from `create_projects_table` (rather than folded into
 * it) so the project's timeline columns have their own independent history,
 * which is useful if they ever need to be rolled back on their own.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->date('start_date')->nullable(); // When work on the project begins; nullable since it's optional.
            $table->date('deadline')->nullable(); // Target completion date; nullable, and validated as on/after start_date in the form request.
        });
    }

    /**
     * Reverse the migrations: remove the two date columns added above.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'deadline']);
        });
    }
};
