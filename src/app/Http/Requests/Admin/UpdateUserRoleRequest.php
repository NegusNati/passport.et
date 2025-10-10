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
            'role' => ['required', 'string', 'in:admin,editor,user'],
        ];
    }

    public function messages(): array
    {
        return [
            'role.in' => 'Role must be one of: admin, editor, or user.',
        ];
    }
}
