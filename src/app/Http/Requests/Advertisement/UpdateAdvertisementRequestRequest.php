<?php

namespace App\Http\Requests\Advertisement;

use App\Domain\Advertisement\Models\AdvertisementRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdvertisementRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::in(AdvertisementRequest::statuses())],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
            'contacted_at' => ['nullable', 'date'],
        ];
    }
}
