<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'featured_image_url' => self::resolvePublicUrl($this->featured_image_url),
            'canonical_url' => $this->canonical_url,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'og_image_url' => self::resolvePublicUrl($this->og_image_url),
            'status' => $this->status,
            'published_at' => optional($this->published_at)->toIso8601String(),
            'reading_time' => (int) $this->reading_time,
            'word_count' => (int) $this->word_count,
            'author' => $this->whenLoaded('author', function () {
                $author = $this->author;

                return [
                    'id' => $author?->id,
                    'first_name' => $author?->first_name,
                    'last_name' => $author?->last_name,
                    'name' => $author ? trim(($author->first_name ?? '').' '.($author->last_name ?? '')) : null,
                    'email' => $author?->email,
                ];
            }),
            'tags' => $this->whenLoaded('tags', fn () => $this->tags->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
            ])),
            'categories' => $this->whenLoaded('categories', fn () => $this->categories->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
            ])),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }

    protected static function resolvePublicUrl(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://', 'data:'])) {
            return $value;
        }

        // Treat as a path on the public disk
        return Storage::disk('public')->url($value);
    }
}
