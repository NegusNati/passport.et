<?php

namespace App\Http\Requests\Advertisement;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdvertisementRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone_number' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:10', 'max:5000'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.required' => 'Phone number is required.',
            'full_name.required' => 'Full name is required.',
            'description.required' => 'Description is required.',
            'description.min' => 'Description must be at least 10 characters.',
            'file.max' => 'File size must not exceed 10MB.',
        ];
    }
}
