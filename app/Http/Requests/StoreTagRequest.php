<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTagRequest extends FormRequest
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
            // Name is mandatory, capped at 50 characters, and must be unique in the tags table.
            'name' => ['required', 'string', 'max:50', 'unique:tags,name'],

            // Color is optional but must be a 6-digit hex code (e.g. #ef4444) when present.
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }
}
