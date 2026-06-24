<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the data submitted when creating a new issue, via
 * IssueController::store(). Every issue must belong to a project, so
 * project_id is required here even though it isn't shown as a column in
 * every view — it's set via the "Project" dropdown on the create form.
 */
class StoreIssueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Anyone hitting this endpoint is allowed to submit the request — issue
     * creation has no ownership restriction in this app.
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
            // Project is mandatory and must reference an existing project row.
            // exists:projects,id prevents a bad/forged project_id from causing a
            // foreign key constraint violation (a raw DB error) at insert time —
            // instead the user gets a clean validation message.
            'project_id' => ['required', 'integer', 'exists:projects,id'],

            // Title is mandatory and capped at 255 characters — every issue
            // needs an identifiable summary, and 255 matches the column size.
            'title' => ['required', 'string', 'max:255'],

            // Description is optional free text, capped at 10000 characters
            // (larger than a project's, since issue write-ups can be detailed).
            'description' => ['nullable', 'string', 'max:10000'],

            // Status is required and must match one of the enum values on the
            // issues table — "in:" keeps invalid statuses out before they ever
            // reach the database's own enum constraint.
            'status' => ['required', 'in:open,in_progress,closed'],

            // Priority is required and must match one of the enum values on the
            // issues table, for the same reason as status above.
            'priority' => ['required', 'in:low,medium,high'],

            // Due date is optional (not every issue has a deadline) but must be
            // a valid, parseable date when present.
            'due_date' => ['nullable', 'date'],
        ];
    }
}
