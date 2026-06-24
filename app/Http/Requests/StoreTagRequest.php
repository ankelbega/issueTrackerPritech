<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the data submitted when creating a new tag, via
 * TagController::store(). Used by both the regular "Create New Tag" form
 * and any future AJAX tag-picker UI.
 */
class StoreTagRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Anyone hitting this endpoint is allowed to submit the request — tags
     * are global and not owned by any particular user.
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
            // Name is mandatory, capped at 50 characters (tags are short labels,
            // not descriptions), and must be unique in the tags table so the
            // same tag can never be created twice.
            'name' => ['required', 'string', 'max:50', 'unique:tags,name'],

            // Color is optional (falls back to a neutral gray in the UI when
            // absent) but must be a 6-digit hex code (e.g. #ef4444) when
            // present, matching what the <input type="color"> picker submits.
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }
}
