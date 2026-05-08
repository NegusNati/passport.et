<?php

namespace App\Http\Requests\AdvertisementCrm;

use App\Domain\Advertisement\Models\Advertisement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAdvertisementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-advertisements') ?? false;
    }

    public function rules(): array
    {
        return [
            'ad_slot_number' => ['nullable', 'string', 'max:80', Rule::unique('advertisements', 'ad_slot_number')->whereNull('deleted_at')],
            'slot_code' => ['required', 'string', 'max:80', 'exists:ad_slots,code'],
            'ad_title' => ['required', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'ad_desc' => ['nullable', 'string', 'max:2000'],
            'ad_excerpt' => ['nullable', 'string', 'max:500'],
            'ad_desktop_asset' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,svg,mp4,webp,avif', 'max:10240'],
            'ad_mobile_asset' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,svg,mp4,webp,avif', 'max:10240'],
            'ad_client_link' => ['nullable', 'url', 'max:255'],
            'target_url' => ['nullable', 'url', 'max:255'],
            'client_name' => ['required', 'string', 'max:255'],
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

        if (! $this->slot_code && $this->ad_slot_number) {
            $this->merge(['slot_code' => $this->ad_slot_number]);
        }

        if (! $this->ad_slot_number && $this->slot_code) {
            $this->merge([
                'ad_slot_number' => Str::slug($this->slot_code).'-'.Str::lower(Str::random(8)),
            ]);
        }

        if (! $this->target_url && $this->ad_client_link) {
            $this->merge(['target_url' => $this->ad_client_link]);
        }

        if (! $this->ad_client_link && $this->target_url) {
            $this->merge(['ad_client_link' => $this->target_url]);
        }

        if (! $this->alt_text && $this->ad_title) {
            $this->merge(['alt_text' => $this->ad_title]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $data = $validator->getData();

            if (! $this->isPublishableStatus($data['status'] ?? null)) {
                return;
            }

            foreach ($this->missingPublishRequirements($data) as $field => $message) {
                $validator->errors()->add($field, $message);
            }

            if (($data['slot_code'] ?? null) && ($data['ad_published_date'] ?? null) && $this->hasScheduleConflict($data)) {
                $validator->errors()->add('slot_code', 'This ad slot already has an active or scheduled advertisement during the selected date range.');
            }
        });
    }

    protected function isPublishableStatus(?string $status): bool
    {
        return in_array($status, [Advertisement::STATUS_ACTIVE, Advertisement::STATUS_SCHEDULED], true);
    }

    protected function missingPublishRequirements(array $data): array
    {
        $missing = [];

        if (empty($data['target_url'])) {
            $missing['target_url'] = 'A target URL is required before an advertisement can be published.';
        }

        if (empty($data['alt_text'])) {
            $missing['alt_text'] = 'Alt text is required before an advertisement can be published.';
        }

        if (! $this->hasFile('ad_desktop_asset')) {
            $missing['ad_desktop_asset'] = 'A desktop asset is required before an advertisement can be published.';
        }

        if (! $this->hasFile('ad_mobile_asset')) {
            $missing['ad_mobile_asset'] = 'A mobile asset is required before an advertisement can be published.';
        }

        return $missing;
    }

    protected function hasScheduleConflict(array $data): bool
    {
        $start = $data['ad_published_date'];
        $end = $data['ad_ending_date'] ?? null;

        return Advertisement::query()
            ->where('slot_code', $data['slot_code'])
            ->whereIn('status', [Advertisement::STATUS_ACTIVE, Advertisement::STATUS_SCHEDULED])
            ->where(function ($query) use ($start) {
                $query->whereNull('ad_ending_date')
                    ->orWhereDate('ad_ending_date', '>=', $start);
            })
            ->when($end, fn ($query) => $query->whereDate('ad_published_date', '<=', $end))
            ->exists();
    }
}
