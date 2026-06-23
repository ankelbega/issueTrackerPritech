<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Project extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'start_date',
        'deadline',
    ];

    /**
     * The attributes that should be cast.
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
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A project has many issues (one-to-many via issues.project_id).
     */
    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    /**
     * A project has many comments through its issues
     * (issues.project_id -> comments.issue_id).
     */
    public function comments(): HasManyThrough
    {
        return $this->hasManyThrough(Comment::class, Issue::class);
    }
}
