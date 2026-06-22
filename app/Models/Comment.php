<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'issue_id',
        'author_name',
        'body',
    ];

    /**
     * A comment belongs to a single issue (comments.issue_id -> issues.id).
     */
    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }
}
