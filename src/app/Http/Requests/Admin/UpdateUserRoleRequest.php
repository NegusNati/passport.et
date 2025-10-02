<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'string', 'in:admin'],
        ];
    }

    public function messages(): array
    {
        return [
            'role.in' => 'Only admin role assignments are currently supported.',
        ];
    }
}
