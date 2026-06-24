<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the data submitted when posting a new comment on an issue, via
 * CommentController::store(). Comments don't require a logged-in author —
 * the author's name is freeform text rather than a linked User account.
 */
class StoreCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Anyone hitting this endpoint is allowed to submit the request —
     * commenting has no ownership restriction in this app.
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
            // Author name is mandatory (a comment needs to be attributed to
            // someone) and capped at 100 characters.
            'author_name' => ['required', 'string', 'max:100'],

            // Comment body is mandatory, must be at least 2 characters (rules
            // out empty/whitespace-only submissions), and capped at 5000
            // characters to keep individual comments reasonably sized.
            'body' => ['required', 'string', 'min:2', 'max:5000'],
        ];
    }
}
