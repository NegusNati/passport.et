<?php

namespace App\Http\Requests\Advertisement;

use App\Domain\Advertisement\Models\AdvertisementRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchAdvertisementRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', Rule::in(AdvertisementRequest::statuses())],
            'created_after' => ['nullable', 'date'],
            'created_before' => ['nullable', 'date'],
            'sort' => ['nullable', 'string'],
            'sort_dir' => ['nullable', 'in:asc,desc'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
