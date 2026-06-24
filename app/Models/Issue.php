<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a single issue (ticket) within a project. This is the central
 * model of the app: it tracks a title/description, a status and priority
 * lifecycle, and can be tagged and assigned to multiple users, with a
 * threaded discussion via comments.
 */
class Issue extends Model
{
    // Lets Issue::factory() be used in seeders/tests to generate fake issues.
    use HasFactory;

    /**
     * Status constants matching the 'status' enum column.
     *
     * Defined here (rather than scattering the raw strings 'open',
     * 'in_progress', 'closed' across controllers/views) so typos are caught
     * at compile time and the valid set of values has one source of truth.
     */
    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_CLOSED = 'closed';

    /**
     * Priority constants matching the 'priority' enum column.
     *
     * Same reasoning as the STATUS_* constants above: a single source of
     * truth for the valid priority values used in validation rules, badges,
     * and filters.
     */
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'project_id', // Which project this issue belongs to; required.
        'title', // Short summary of the issue.
        'description', // Optional longer details.
        'status', // Current workflow state (open/in_progress/closed).
        'priority', // Urgency level (low/medium/high).
        'due_date', // Optional target resolution date.
    ];

    /**
     * The attributes that should be cast.
     *
     * Casting due_date to 'date' returns a Carbon instance (so the view can
     * call ->format('M d, Y')) and lets us safely use ?-> when it's null.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
        ];
    }

    /**
     * An issue belongs to a single project (issues.project_id -> projects.id).
     *
     * Lets views show "which project is this issue in" and lets the
     * controller redirect back to the parent project after deleting an issue.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * An issue has many comments (one-to-many via comments.issue_id).
     *
     * Powers the discussion thread shown on the issue's detail page.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * An issue can have many tags, and a tag can belong to many issues
     * (many-to-many via the issue_tag pivot table).
     *
     * Lets issues be categorized/filtered by labels like "Bug" or "Frontend"
     * without limiting an issue to just one category.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'issue_tag');
    }

    /**
     * An issue can be assigned to many users, and a user can be assigned
     * to many issues (many-to-many via the issue_user pivot table).
     *
     * Models real-world team workflows where more than one person may be
     * responsible for resolving a single issue.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'issue_user');
    }
}
