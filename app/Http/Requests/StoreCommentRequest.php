<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
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
            // Author name is mandatory and capped at 100 characters.
            'author_name' => ['required', 'string', 'max:100'],

            // Comment body is mandatory, must be at least 2 characters, and capped at 5000 characters.
            'body' => ['required', 'string', 'min:2', 'max:5000'],
        ];
    }
}
