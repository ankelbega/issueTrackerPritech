<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Default Laravel starter migration for the database cache driver.
 *
 * These tables let Laravel's cache system (Cache::put/get, etc.) store its
 * data in the database instead of a dedicated cache server like Redis. This
 * app doesn't rely on this driver day-to-day, but it ships with every fresh
 * Laravel install so CACHE_STORE=database works out of the box if needed.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Stores cached key/value pairs when using the "database" cache driver.
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary(); // The cache key being stored.
            $table->mediumText('value'); // The serialized cached value.
            $table->integer('expiration'); // Unix timestamp of when this entry expires.
        });

        // Prevents race conditions when multiple processes try to write/refresh
        // the same cache key at the same time (used by Cache::lock()).
        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary(); // The lock's identifier.
            $table->string('owner'); // Unique token identifying who currently holds the lock.
            $table->integer('expiration'); // Unix timestamp of when the lock auto-releases.
        });
    }

    /**
     * Reverse the migrations: drop both cache tables.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
