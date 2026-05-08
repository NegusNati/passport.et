<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdvertisementResource extends JsonResource
{
    public function toArray($request): array
    {
        $isAdmin = $request->user()?->can('manage-advertisements');

        return [
            'id' => $this->id,
            'ad_slot_number' => $this->ad_slot_number,
            'slot_code' => $this->slot_code,
            'ad_title' => $this->ad_title,
            'alt_text' => $this->alt_text,
            'ad_desc' => $this->ad_desc,
            'ad_excerpt' => $this->ad_excerpt,
            'ad_desktop_asset' => self::resolvePublicUrl($this->ad_desktop_asset),
            'desktop_width' => $this->desktop_width,
            'desktop_height' => $this->desktop_height,
            'ad_mobile_asset' => self::resolvePublicUrl($this->ad_mobile_asset),
            'mobile_width' => $this->mobile_width,
            'mobile_height' => $this->mobile_height,
            'ad_client_link' => $this->ad_client_link,
            'target_url' => $this->target_url,
            'status' => $this->status,
            'package_type' => $this->package_type,
            'ad_published_date' => optional($this->ad_published_date)->toDateString(),
            'ad_ending_date' => optional($this->ad_ending_date)->toDateString(),
            'payment_status' => $this->payment_status,
            'payment_amount' => $this->payment_amount,
            'client_name' => $this->client_name,
            'impressions_count' => $this->impressions_count,
            'clicks_count' => $this->clicks_count,
            'priority' => $this->priority,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
            $this->mergeWhen($isAdmin, [
                'admin_notes' => $this->admin_notes,
                'expiry_notification_sent' => $this->expiry_notification_sent,
                'days_until_expiry' => $this->daysUntilExpiry(),
                'is_active' => $this->isActive(),
                'is_expired' => $this->isExpired(),
                'advertisement_request_id' => $this->advertisement_request_id,
            ]),
        ];
    }

    protected static function resolvePublicUrl(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        return Storage::disk('public')->url($value);
    }
}
