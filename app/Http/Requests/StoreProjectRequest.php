<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the data submitted when creating a new project, via
 * ProjectController::store(). Authorization for *who* may create a project
 * isn't restricted here (every logged-in user can); ownership is instead
 * assigned automatically in the controller via auth()->id().
 */
class StoreProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Anyone hitting this endpoint is allowed to submit the request — there's
     * no per-project ownership to check yet since the project doesn't exist.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Project name is mandatory and capped at 255 characters — every
            // project needs an identifiable title, and 255 matches the
            // underlying VARCHAR column size to avoid silent truncation.
            'name' => ['required', 'string', 'max:255'],

            // Description is optional free text, capped at 5000 characters so a
            // single project description can't bloat the database or the page.
            'description' => ['nullable', 'string', 'max:5000'],

            // Start date is optional (not every project has been scheduled yet)
            // but must be a valid, parseable date when present.
            'start_date' => ['nullable', 'date'],

            // Deadline is optional, must be a valid date, and cannot be before
            // start_date — prevents nonsensical timelines like a deadline that
            // precedes the project's own start.
            'deadline' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }
}
