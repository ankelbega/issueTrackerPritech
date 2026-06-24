<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Represents a project: the top-level container that groups related issues
 * together. Each project is optionally owned by a user (the creator), and
 * can have a start date / deadline for planning purposes.
 */
class Project extends Model
{
    // Lets Project::factory() be used in seeders/tests to generate fake projects.
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * Listing these explicitly (instead of using $guarded = []) means
     * Project::create($request->validated()) can never accidentally save a
     * field that wasn't intended to be user-settable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id', // Set automatically by the controller to the logged-in user, not user input.
        'name', // The project's title.
        'description', // Optional longer description.
        'start_date', // Optional planning start date.
        'deadline', // Optional planning end date.
    ];

    /**
     * The attributes that should be cast.
     *
     * Casting start_date/deadline to 'date' means accessing them returns
     * Carbon instances (so views can call ->format('M d, Y')) instead of
     * raw strings, and lets us safely call ?-> on them when they're null.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'deadline' => 'date',
        ];
    }

    /**
     * A project belongs to the user who owns it (projects.user_id -> users.id).
     *
     * This exists so the ProjectPolicy can check "is the current user the
     * owner of this project?" before allowing them to edit or delete it.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A project has many issues (one-to-many via issues.project_id).
     *
     * This is the core relationship of the app: every issue must belong to
     * a project, so this lets us list/paginate a project's issues and
     * cascade-delete them when the project itself is deleted.
     */
    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    /**
     * A project has many comments through its issues
     * (issues.project_id -> comments.issue_id).
     *
     * Useful for aggregating "all activity on this project" without having
     * to manually loop over every issue and merge their comments.
     */
    public function comments(): HasManyThrough
    {
        return $this->hasManyThrough(Comment::class, Issue::class);
    }
}
