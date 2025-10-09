<?php

namespace App\Http\Requests\Admin;

use App\Support\UserFilters;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'max:255'],
            'phone_number' => ['sometimes', 'string', 'max:30'],
            'role' => ['sometimes', 'string', 'max:100'],
            'plan' => ['sometimes', 'string', 'max:100'],
            'is_admin' => ['sometimes'],
            'email_verified' => ['sometimes'],
            'created_from' => ['sometimes', 'date'],
            'created_to' => ['sometimes', 'date'],
            'sort' => ['sometimes', 'string', Rule::in(UserFilters::sortableColumns())],
            'direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
