<?php

namespace App\Http\Requests\Passport;

use Illuminate\Foundation\Http\FormRequest;

class SearchPassportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'request_number' => ['nullable', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'published_after' => ['nullable', 'date'],
            'published_before' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
            'page' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
            'sort' => ['nullable', 'string', 'max:50'],
            'sort_dir' => ['nullable', 'in:asc,desc'],
        ];
    }

    public function messages(): array
    {
        return [
            // Placeholder: customize validation messages when implementing Phase 3.
        ];
    }
}
