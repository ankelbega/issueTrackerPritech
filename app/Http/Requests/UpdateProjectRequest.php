<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    /**
     * Anyone hitting this endpoint is allowed to submit the request.
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

            // Deadline is optional, must be a valid date, and cannot be before start_date.
            'deadline' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }
}
