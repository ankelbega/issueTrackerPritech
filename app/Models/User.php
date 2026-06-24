<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Represents an authenticated user/account. Beyond the standard Laravel
 * Breeze authentication fields, a User in this app can own projects and be
 * assigned to work on issues.
 */
class User extends Authenticatable
{
    // HasFactory lets User::factory() generate fake users in seeders/tests.
    // Notifiable lets the app send Laravel notifications (e.g. password reset emails) to this user.
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name', // Display name, shown in assignee avatars/initials and the sidebar footer.
        'email', // Login identifier; must be unique (enforced at the DB level).
        'password', // Hashed password; never stored or displayed in plain text.
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * Ensures that if a User model is ever converted to JSON/array (e.g. in
     * an API response), the password hash and remember-me token are never
     * accidentally exposed.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * 'datetime' on email_verified_at gives a Carbon instance instead of a
     * raw string. 'hashed' on password automatically hashes the value with
     * Hash::make() whenever it's set, so controllers/seeders can assign a
     * plain-text password and it's never stored unhashed.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * A user can be assigned to many issues, and an issue can be assigned
     * to many users (many-to-many via the issue_user pivot table).
     *
     * Powers the "Assigned Users" section on an issue's detail page and
     * lets a user's workload be queried later if needed.
     */
    public function issues(): BelongsToMany
    {
        return $this->belongsToMany(Issue::class, 'issue_user');
    }

    /**
     * A user can own many projects (projects.user_id -> users.id).
     *
     * This is the inverse of Project::user(); used by the seeder to assign
     * specific projects to specific users, and could be used to build a
     * "my projects" view in the future.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
