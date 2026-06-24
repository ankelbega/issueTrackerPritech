<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a single comment left on an issue. Comments record the
 * author's name as plain text rather than linking to a User account, since
 * the comment form is intentionally open (no login required to comment).
 */
class Comment extends Model
{
    // Lets Comment::factory() be used in seeders/tests to generate fake comments.
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'issue_id', // Which issue this comment was left on; required.
        'author_name', // Free-text name of the commenter.
        'body', // The comment's text content.
    ];

    /**
     * A comment belongs to a single issue (comments.issue_id -> issues.id).
     *
     * Lets us navigate from a comment back to its parent issue if ever
     * needed (e.g. for a notification or audit feature).
     */
    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }
}
