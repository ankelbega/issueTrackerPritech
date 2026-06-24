<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Represents a reusable label (e.g. "Bug", "Frontend") that can be attached
 * to any number of issues. Tags are global — created once and shared across
 * every project/issue, rather than being scoped to a single project.
 */
class Tag extends Model
{
    // Lets Tag::factory() be used in seeders/tests to generate fake tags.
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name', // The tag's label text; must be unique (enforced at the DB level).
        'color', // Optional hex color (e.g. "#ef4444") used to render the tag's pill in the UI.
    ];

    /**
     * A tag can belong to many issues, and an issue can have many tags
     * (many-to-many via the issue_tag pivot table).
     *
     * Lets the "Tags" index page show how many issues use each tag
     * (via withCount('issues')) without loading every issue into memory.
     */
    public function issues(): BelongsToMany
    {
        return $this->belongsToMany(Issue::class, 'issue_tag');
    }
}
