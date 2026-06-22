<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Issue extends Model
{
    use HasFactory;

    /**
     * Status constants matching the 'status' enum column.
     */
    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_CLOSED = 'closed';

    /**
     * Priority constants matching the 'priority' enum column.
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
        'project_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
    ];

    /**
     * The attributes that should be cast.
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
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * An issue has many comments (one-to-many via comments.issue_id).
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * An issue can have many tags, and a tag can belong to many issues
     * (many-to-many via the issue_tag pivot table).
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'issue_tag');
    }

    /**
     * An issue can be assigned to many users, and a user can be assigned
     * to many issues (many-to-many via the issue_user pivot table).
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'issue_user');
    }
}
