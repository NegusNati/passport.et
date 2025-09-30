<?php

namespace App\Http\Requests\Passport;

use App\Support\PassportFilters;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchPassportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'request_number' => ['nullable', 'string', 'min:3', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'query' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'published_after' => ['nullable', 'date'],
            'published_before' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', Rule::in(PassportFilters::pageSizeOptions())],
            'page_size' => ['nullable', 'integer', Rule::in(PassportFilters::pageSizeOptions())],
            'page' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
            'sort' => ['nullable', 'string', 'max:50'],
            'sort_dir' => ['nullable', 'in:asc,desc'],
        ];
    }

    public function messages(): array
    {
        return [
            'request_number.min' => 'The request number must be at least 3 characters to enable indexed search.',
        ];
    }
}
