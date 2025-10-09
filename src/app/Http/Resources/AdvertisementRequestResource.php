<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdvertisementRequestResource extends JsonResource
{
    public function toArray($request): array
    {
        $isAdmin = $request->user()?->can('manage-advertisements');

        return [
            'id' => $this->id,
            'phone_number' => $this->phone_number,
            'email' => $this->email,
            'full_name' => $this->full_name,
            'company_name' => $this->company_name,
            'description' => $this->description,
            'file_url' => self::resolvePublicUrl($this->file_path),
            'status' => $this->status,
            'contacted_at' => optional($this->contacted_at)->toIso8601String(),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
            $this->mergeWhen($isAdmin, [
                'admin_notes' => $this->admin_notes,
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
