<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdvertisementPublicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $slot = $this->resource->relationLoaded('adSlot') ? $this->adSlot : null;
        $slotCode = $this->publicSlotCode();
        $targetUrl = $this->publicTargetUrl();

        return [
            'id' => $this->id,
            'slot_code' => $slotCode,
            'placement' => $slotCode,
            'title' => $this->ad_title,
            'alt_text' => $this->publicAltText(),
            'target_url' => $targetUrl,
            'client_link' => $targetUrl,
            'desktop_asset' => [
                'url' => self::resolvePublicUrl($this->ad_desktop_asset),
                'width' => $this->desktop_width ?: $slot?->desktop_width ?: 1200,
                'height' => $this->desktop_height ?: $slot?->desktop_height ?: 300,
            ],
            'mobile_asset' => [
                'url' => self::resolvePublicUrl($this->ad_mobile_asset ?: $this->ad_desktop_asset),
                'width' => $this->mobile_width ?: $slot?->mobile_width ?: 640,
                'height' => $this->mobile_height ?: $slot?->mobile_height ?: 360,
            ],
            'impression_url' => route('api.v1.advertisements.impression', $this->resource, false),
            'click_url' => route('api.v1.advertisements.click', $this->resource, false),
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
