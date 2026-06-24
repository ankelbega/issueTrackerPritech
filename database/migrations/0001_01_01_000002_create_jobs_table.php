<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Default Laravel starter migration for the database queue driver.
 *
 * These tables let Laravel's queue system (dispatching jobs to run in the
 * background) store pending/failed jobs in the database instead of a
 * dedicated queue server like Redis or SQS. Ships with every fresh Laravel
 * install; this app doesn't currently dispatch any queued jobs itself.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Holds jobs waiting to be processed by a queue worker.
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index(); // Which named queue this job belongs to.
            $table->longText('payload'); // Serialized job class + data.
            $table->unsignedTinyInteger('attempts'); // How many times this job has been attempted so far.
            $table->unsignedInteger('reserved_at')->nullable(); // When a worker picked this job up (null if still waiting).
            $table->unsignedInteger('available_at'); // When this job becomes eligible to run (supports delayed jobs).
            $table->unsignedInteger('created_at'); // When the job was originally queued.
        });

        // Tracks groups of jobs dispatched together as a "batch" (Bus::batch()),
        // so progress/completion/failure can be queried as a single unit.
        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary(); // UUID identifying the batch.
            $table->string('name'); // Human-readable batch name.
            $table->integer('total_jobs'); // Total number of jobs in the batch.
            $table->integer('pending_jobs'); // Jobs not yet finished.
            $table->integer('failed_jobs'); // Jobs that failed.
            $table->longText('failed_job_ids'); // Serialized list of failed job IDs.
            $table->mediumText('options')->nullable(); // Serialized batch options/callbacks.
            $table->integer('cancelled_at')->nullable(); // Timestamp if the batch was cancelled.
            $table->integer('created_at'); // When the batch was created.
            $table->integer('finished_at')->nullable(); // When the batch finished (null while still running).
        });

        // Records jobs that exhausted their retry attempts and ultimately failed,
        // so they can be inspected or retried manually later.
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique(); // Unique identifier for this failure record.
            $table->text('connection'); // Which queue connection the job ran on.
            $table->text('queue'); // Which named queue the job came from.
            $table->longText('payload'); // Serialized job class + data at the time of failure.
            $table->longText('exception'); // The exception message/stack trace that caused the failure.
            $table->timestamp('failed_at')->useCurrent(); // When the failure was recorded.
        });
    }

    /**
     * Reverse the migrations: drop all three queue-related tables.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};
