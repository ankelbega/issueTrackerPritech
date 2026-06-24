<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the data submitted when editing an existing issue, via
 * IssueController::update(). The rules intentionally mirror
 * StoreIssueRequest exactly, since editing an issue should be held to the
 * same data-quality standard as creating one.
 */
class UpdateIssueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Anyone hitting this endpoint is allowed to submit the request — issue
     * editing has no ownership restriction in this app.
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
            // Without this, reassigning an issue to a different project via the
            // edit form silently did nothing, since project_id was never validated
            // and therefore never present in $request->validated().
            'project_id' => ['required', 'integer', 'exists:projects,id'],

            // Title is mandatory and capped at 255 characters.
            'title' => ['required', 'string', 'max:255'],

            // Description is optional free text, capped at 10000 characters.
            'description' => ['nullable', 'string', 'max:10000'],

            // Status is required and must match one of the enum values on the issues table.
            'status' => ['required', 'in:open,in_progress,closed'],

            // Priority is required and must match one of the enum values on the issues table.
            'priority' => ['required', 'in:low,medium,high'],

            // Due date is optional but must be a valid date when present.
            'due_date' => ['nullable', 'date'],
        ];
    }
}
