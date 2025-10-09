<?php

namespace App\Http\Requests\AdvertisementCrm;

use App\Domain\Advertisement\Models\Advertisement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdvertisementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-advertisements') ?? false;
    }

    public function rules(): array
    {
        return [
            'ad_slot_number' => ['required', 'string', 'max:50', Rule::unique('advertisements', 'ad_slot_number')->whereNull('deleted_at')],
            'ad_title' => ['required', 'string', 'max:255'],
            'ad_desc' => ['nullable', 'string', 'max:2000'],
            'ad_excerpt' => ['nullable', 'string', 'max:500'],
            'ad_desktop_asset' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,svg,mp4,webp', 'max:10240'],
            'ad_mobile_asset' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,svg,mp4,webp', 'max:10240'],
            'ad_client_link' => ['nullable', 'url', 'max:255'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'package_type' => ['required', Rule::in(Advertisement::packageTypes())],
            'ad_published_date' => ['required', 'date', 'after_or_equal:today'],
            'ad_ending_date' => ['nullable', 'date', 'after:ad_published_date'],
            'status' => ['required', Rule::in(Advertisement::statuses())],
            'payment_status' => ['required', Rule::in(Advertisement::paymentStatuses())],
            'payment_amount' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'priority' => ['nullable', 'integer', 'between:0,100'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
            'advertisement_request_id' => ['nullable', 'integer', 'exists:advertisement_requests,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'ad_published_date.after_or_equal' => 'The publication date must be today or in the future.',
            'ad_ending_date.after' => 'The ending date must be after the publication date.',
            'ad_desktop_asset.max' => 'The desktop asset must not exceed 10MB.',
            'ad_mobile_asset.max' => 'The mobile asset must not exceed 10MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Ensure status consistency with payment status
        if ($this->status === Advertisement::STATUS_ACTIVE && $this->payment_status === Advertisement::PAYMENT_PENDING) {
            // Auto-correct to scheduled or draft if payment is pending
            $this->merge(['status' => Advertisement::STATUS_SCHEDULED]);
        }
    }
}
