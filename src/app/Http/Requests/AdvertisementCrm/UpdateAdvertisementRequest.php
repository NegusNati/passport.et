<?php

namespace App\Http\Requests\AdvertisementCrm;

use App\Domain\Advertisement\Models\Advertisement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'ad_slot_number' => ['sometimes', 'nullable', 'string', 'max:80', Rule::unique('advertisements', 'ad_slot_number')->ignore($advertisementId)->whereNull('deleted_at')],
            'slot_code' => ['sometimes', 'nullable', 'string', 'max:80', 'exists:ad_slots,code'],
            'ad_title' => ['sometimes', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'ad_desc' => ['nullable', 'string', 'max:2000'],
            'ad_excerpt' => ['nullable', 'string', 'max:500'],
            'ad_desktop_asset' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,svg,mp4,webp,avif', 'max:10240'],
            'ad_mobile_asset' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,svg,mp4,webp,avif', 'max:10240'],
            'ad_client_link' => ['nullable', 'url', 'max:255'],
            'target_url' => ['nullable', 'url', 'max:255'],
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
            'remove_ad_desktop_asset' => ['nullable', 'boolean'],
            'remove_ad_mobile_asset' => ['nullable', 'boolean'],
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
        $advertisement = $this->route('advertisement');

        if ($this->has('ad_slot_number') && ! $this->ad_slot_number) {
            $this->merge(['ad_slot_number' => $advertisement->ad_slot_number]);
        }

        if ($this->has('ad_slot_number') && ! $this->slot_code) {
            $this->merge(['slot_code' => $this->ad_slot_number]);
        }

        if ($this->has('ad_client_link') && ! $this->target_url) {
            $this->merge(['target_url' => $this->ad_client_link]);
        }

        if ($this->has('target_url') && ! $this->ad_client_link) {
            $this->merge(['ad_client_link' => $this->target_url]);
        }

        if ($this->has('ad_title') && ! $this->alt_text) {
            $this->merge(['alt_text' => $this->ad_title]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $advertisement = $this->route('advertisement');
            $data = $validator->getData();
            $merged = $this->mergedAdvertisementData($advertisement, $data);

            if (! $this->isPublishableStatus($merged['status'] ?? null)) {
                return;
            }

            foreach ($this->missingPublishRequirements($advertisement, $merged, $data) as $field => $message) {
                $validator->errors()->add($field, $message);
            }

            if (($merged['slot_code'] ?? null) && ($merged['ad_published_date'] ?? null) && $this->hasScheduleConflict($advertisement, $merged)) {
                $validator->errors()->add('slot_code', 'This ad slot already has an active or scheduled advertisement during the selected date range.');
            }
        });
    }

    protected function mergedAdvertisementData(Advertisement $advertisement, array $data): array
    {
        return [
            'slot_code' => $data['slot_code'] ?? $advertisement->publicSlotCode(),
            'target_url' => $data['target_url'] ?? $advertisement->publicTargetUrl(),
            'alt_text' => $data['alt_text'] ?? $advertisement->publicAltText(),
            'status' => $data['status'] ?? $advertisement->status,
            'ad_published_date' => $data['ad_published_date'] ?? $advertisement->ad_published_date?->toDateString(),
            'ad_ending_date' => array_key_exists('ad_ending_date', $data)
                ? $data['ad_ending_date']
                : $advertisement->ad_ending_date?->toDateString(),
        ];
    }

    protected function isPublishableStatus(?string $status): bool
    {
        return in_array($status, [Advertisement::STATUS_ACTIVE, Advertisement::STATUS_SCHEDULED], true);
    }

    protected function missingPublishRequirements(Advertisement $advertisement, array $merged, array $data): array
    {
        $missing = [];
        $hasDesktop = $this->hasFile('ad_desktop_asset')
            || (! $this->boolean('remove_ad_desktop_asset') && filled($advertisement->ad_desktop_asset));
        $hasMobile = $this->hasFile('ad_mobile_asset')
            || (! $this->boolean('remove_ad_mobile_asset') && filled($advertisement->ad_mobile_asset));

        if (empty($merged['slot_code'])) {
            $missing['slot_code'] = 'An ad slot is required before an advertisement can be published.';
        }

        if (empty($merged['target_url'])) {
            $missing['target_url'] = 'A target URL is required before an advertisement can be published.';
        }

        if (empty($merged['alt_text'])) {
            $missing['alt_text'] = 'Alt text is required before an advertisement can be published.';
        }

        if (! $hasDesktop) {
            $missing['ad_desktop_asset'] = 'A desktop asset is required before an advertisement can be published.';
        }

        if (! $hasMobile) {
            $missing['ad_mobile_asset'] = 'A mobile asset is required before an advertisement can be published.';
        }

        return $missing;
    }

    protected function hasScheduleConflict(Advertisement $advertisement, array $data): bool
    {
        $start = $data['ad_published_date'];
        $end = $data['ad_ending_date'] ?? null;

        return Advertisement::query()
            ->whereKeyNot($advertisement->id)
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
