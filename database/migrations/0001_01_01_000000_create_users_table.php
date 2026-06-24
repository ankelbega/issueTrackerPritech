<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * This is one of Laravel/Breeze's default starter migrations.
 *
 * It creates the three tables needed for authentication to work:
 * - `users`: stores each person's login credentials and profile basics.
 * - `password_reset_tokens`: stores temporary tokens used by the
 *   "forgot password" email flow.
 * - `sessions`: stores server-side session data (used when SESSION_DRIVER
 *   is set to "database").
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Main authentication table: every logged-in user (including the
        // ones who own projects and get assigned to issues) is a row here.
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key.
            $table->string('name'); // Display name shown in the UI (e.g. assignee avatars).
            $table->string('email')->unique(); // Login identifier; must be unique across all users.
            $table->timestamp('email_verified_at')->nullable(); // Set once the user confirms their email; null until then.
            $table->string('password'); // Hashed password (never stored in plain text).
            $table->rememberToken(); // Token used for "remember me" persistent login cookies.
            $table->timestamps(); // created_at / updated_at columns.
        });

        // Stores short-lived tokens emailed to a user when they request a
        // password reset, so the reset link can be verified before allowing
        // a new password to be set.
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary(); // The email the reset was requested for; doubles as the lookup key.
            $table->string('token'); // Hashed token sent in the reset email link.
            $table->timestamp('created_at')->nullable(); // Used to expire old/stale reset requests.
        });

        // Stores active sessions when the app is configured to persist
        // sessions in the database instead of files/cookies only.
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary(); // The session ID (matches the session cookie value).
            $table->foreignId('user_id')->nullable()->index(); // Which user this session belongs to, if logged in.
            $table->string('ip_address', 45)->nullable(); // IP address the session was created from (IPv6-safe length).
            $table->text('user_agent')->nullable(); // Browser/user agent string, useful for "active sessions" UIs.
            $table->longText('payload'); // Serialized session data.
            $table->integer('last_activity')->index(); // Unix timestamp of last activity, used to expire idle sessions.
        });
    }

    /**
     * Reverse the migrations: drop all three tables created above.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
