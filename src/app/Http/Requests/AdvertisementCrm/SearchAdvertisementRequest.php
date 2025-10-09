<?php

namespace App\Http\Requests\AdvertisementCrm;

use Illuminate\Foundation\Http\FormRequest;

class SearchAdvertisementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-advertisements') ?? false;
    }

    public function rules(): array
    {
        return [
            'ad_title' => ['nullable', 'string', 'max:255'],
            'ad_slot_number' => ['nullable', 'string', 'max:50'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:draft,active,paused,expired,scheduled'],
            'payment_status' => ['nullable', 'string', 'in:pending,paid,refunded,failed'],
            'package_type' => ['nullable', 'string', 'in:weekly,monthly,yearly'],
            'published_after' => ['nullable', 'date'],
            'published_before' => ['nullable', 'date'],
            'ending_after' => ['nullable', 'date'],
            'ending_before' => ['nullable', 'date'],
            'sort' => ['nullable', 'string'],
            'sort_dir' => ['nullable', 'string', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'paginate' => ['nullable', 'boolean'],
        ];
    }
}
