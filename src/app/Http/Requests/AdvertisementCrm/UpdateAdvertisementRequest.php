<?php

namespace App\Http\Requests\AdvertisementCrm;

use App\Domain\Advertisement\Models\Advertisement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdvertisementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-advertisements') ?? false;
    }

    public function rules(): array
    {
        $advertisementId = $this->route('advertisement')->id ?? null;

        return [
            'ad_slot_number' => ['sometimes', 'string', 'max:50', Rule::unique('advertisements', 'ad_slot_number')->ignore($advertisementId)->whereNull('deleted_at')],
            'ad_title' => ['sometimes', 'string', 'max:255'],
            'ad_desc' => ['nullable', 'string', 'max:2000'],
            'ad_excerpt' => ['nullable', 'string', 'max:500'],
            'ad_desktop_asset' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,svg,mp4,webp', 'max:10240'],
            'ad_mobile_asset' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,svg,mp4,webp', 'max:10240'],
            'ad_client_link' => ['nullable', 'url', 'max:255'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'package_type' => ['sometimes', Rule::in(Advertisement::packageTypes())],
            'ad_published_date' => ['sometimes', 'date'],
            'ad_ending_date' => ['nullable', 'date', 'after:ad_published_date'],
            'status' => ['sometimes', Rule::in(Advertisement::statuses())],
            'payment_status' => ['sometimes', Rule::in(Advertisement::paymentStatuses())],
            'payment_amount' => ['sometimes', 'numeric', 'min:0', 'max:999999.99'],
            'priority' => ['nullable', 'integer', 'between:0,100'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
            'advertisement_request_id' => ['nullable', 'integer', 'exists:advertisement_requests,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'ad_ending_date.after' => 'The ending date must be after the publication date.',
            'ad_desktop_asset.max' => 'The desktop asset must not exceed 10MB.',
            'ad_mobile_asset.max' => 'The mobile asset must not exceed 10MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Prevent activating an ad with pending payment
        $advertisement = $this->route('advertisement');
        
        if ($this->status === Advertisement::STATUS_ACTIVE) {
            $currentPaymentStatus = $this->payment_status ?? $advertisement->payment_status;
            
            if ($currentPaymentStatus === Advertisement::PAYMENT_PENDING) {
                $this->merge(['status' => Advertisement::STATUS_SCHEDULED]);
            }
        }
    }
}
