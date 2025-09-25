<?php

namespace App\Domain\Article\Models;

use App\Domain\Article\Data\ArticleSearchParams;
use App\Support\ArticleFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image_url',
        'canonical_url',
        'meta_title',
        'meta_description',
        'og_image_url',
        'status',
        'published_at',
        'reading_time',
        'word_count',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // Relationships
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function author()
    {
        return $this->belongsTo(\App\Models\User::class, 'author_id');
    }

    // Scopes
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeFilter(Builder $query, ArticleSearchParams $params): Builder
    {
        $filters = $params->filters();

        if ($filters['q']) {
            $query->where(function (Builder $q) use ($filters) {
                $q->where('title', 'like', $filters['q'].'%')
                  ->orWhere('title', 'like', '%'.$filters['q'].'%')
                  ->orWhere('excerpt', 'like', '%'.$filters['q'].'%');
                // MySQL fulltext index will help if available
            });
        }

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        } else {
            // Public default: published only
            if ($params->context() === 'api_public') {
                $query->published();
            }
        }

        if ($filters['published_after']) {
            $query->whereDate('published_at', '>=', $filters['published_after']);
        }

        if ($filters['published_before']) {
            $query->whereDate('published_at', '<=', $filters['published_before']);
        }

        if ($filters['category']) {
            $query->whereHas('categories', fn (Builder $q) => $q->where('slug', $filters['category']));
        }

        if ($filters['tag']) {
            $query->whereHas('tags', fn (Builder $q) => $q->where('slug', $filters['tag']));
        }

        if ($filters['author_id']) {
            $query->where('author_id', $filters['author_id']);
        }

        return $query;
    }

    public function scopeSort(Builder $query, ArticleSearchParams $params): Builder
    {
        [$column, $direction] = $params->sort();

        if (! in_array($column, ArticleFilters::sortableColumns(), true)) {
            [$column, $direction] = ArticleFilters::defaultSort();
        }

        return $query->orderBy($column, $direction);
    }

    public function scopeLimitForSearch(Builder $query, ArticleSearchParams $params): Builder
    {
        $limit = $params->limit() ?? ArticleFilters::defaultLimit();
        return $query->limit($limit);
    }

    // Helpers
    public static function makeUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$i++;
        }
        return $slug;
    }
}

