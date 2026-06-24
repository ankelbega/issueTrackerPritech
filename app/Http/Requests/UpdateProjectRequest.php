<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the data submitted when editing an existing project, via
 * ProjectController::update(). The rules intentionally mirror
 * StoreProjectRequest exactly, since editing a project should be held to
 * the same data-quality standard as creating one. Ownership authorization
 * (only the project's owner may update it) is handled separately by
 * $this->authorize('update', $project) in the controller, via ProjectPolicy.
 */
class UpdateProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Always true here — the actual ownership check happens in the
     * controller via the ProjectPolicy, not in this Form Request.
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
            // Project name is mandatory and capped at 255 characters.
            'name' => ['required', 'string', 'max:255'],

            // Description is optional free text, capped at 5000 characters.
            'description' => ['nullable', 'string', 'max:5000'],

            // Start date is optional but must be a valid date when present.
            'start_date' => ['nullable', 'date'],

            // Deadline is optional, must be a valid date, and cannot be before
            // start_date — prevents nonsensical timelines like a deadline that
            // precedes the project's own start.
            'deadline' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }
}
