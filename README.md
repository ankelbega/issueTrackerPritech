# Issue Tracker

A multi-project issue tracking application built with Laravel. It lets teams organize work into projects, break that work down into issues with a status and priority lifecycle, categorize issues with colored tags, assign issues to team members, and discuss progress through comments — all without ever leaving the page, thanks to AJAX-powered interactions for tagging, assignment, and commenting.

## Features

- Project management (create, edit, delete, view)
- Project ownership — only the creator can edit or delete their project
- Issue tracking with status (Open, In Progress, Closed) and priority (Low, Medium, High)
- Filter issues by status, priority, and tag
- Tag management — create tags with custom colors, attach/detach tags to issues via AJAX (no page reload)
- Comments — add and load comments via AJAX with pagination
- User assignment — assign/unassign team members to issues via AJAX
- Authorization — policy-based ownership (only project owners can edit/delete their project)
- Full form validation with inline error messages
- Flash messages for all create/update/delete actions
- Clean UI built with Blade templates and Alpine.js

## Tech Stack

- Laravel 11
- PHP 8.5
- SQLite
- Laravel Breeze (Blade stack) for authentication
- Alpine.js for lightweight interactivity
- Custom CSS (no Tailwind in the final UI)
- Vite for asset bundling

## Requirements

- PHP >= 8.2
- Composer
- Node.js & npm

## Installation

1. Clone the repository: `git clone https://github.com/ankelbega/issueTrackerPritech.git`
2. `cd issueTrackerPritech`
3. `composer install`
4. `npm install && npm run build`
5. `cp .env.example .env`
6. `php artisan key:generate`
7. Create the SQLite file: `touch database/database.sqlite` (Windows: `type nul > database\database.sqlite`)
8. `php artisan migrate`
9. `php artisan db:seed`
10. `php artisan serve`
11. Visit http://localhost:8000

## Demo Credentials

| Role          | Email           | Password |
|---------------|-----------------|----------|
| Admin user    | admin@test.com  | password |
| Regular user  | user@test.com   | password |

Note: admin@test.com owns 3 projects, user@test.com owns 2 projects. Each user can only edit/delete their own projects.

## Database Schema

- `projects` (id, name, description, start_date, deadline, user_id, timestamps)
- `issues` (id, project_id, title, description, status, priority, due_date, timestamps)
- `tags` (id, name, color, timestamps)
- `comments` (id, issue_id, author_name, body, timestamps)
- `issue_tag` — pivot (issue_id, tag_id)
- `issue_user` — pivot (issue_id, user_id)

## Project Structure

- `app/Http/Controllers` — resource controllers for each entity
- `app/Http/Requests` — form request validation classes
- `app/Models` — Eloquent models with relationships
- `app/Policies` — ProjectPolicy for ownership authorization
- `database/migrations` — all table migrations
- `database/seeders` — DatabaseSeeder with demo data
- `resources/views` — Blade templates organized by entity
- `public/css/app.css` — custom design system

## Key Implementation Notes

- All routes protected by auth middleware
- Eager loading used throughout to prevent N+1 queries
- AJAX interactions use fetch() with proper error handling and inline error messages
- Alpine.js x-data uses single-quote delimiters to safely wrap Laravel @json() output
- ProjectPolicy enforces ownership-based authorization
- Foreign key indexes added manually (SQLite does not auto-index foreign keys)

## Git History

The project was built with logical commits, one per feature phase, following a structured development workflow.

---

Built as a technical assessment for PRITECH.
