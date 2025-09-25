<?php

namespace App\Domain\Article\Data;

use App\Support\ArticleFilters;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Throwable;

class ArticleSearchParams
{
    public function __construct(
        protected string $context, // api_public|api_admin
        protected ?string $title,
        protected ?string $q,
        protected ?string $category,
        protected ?string $tag,
        protected ?string $status,
        protected ?int $authorId,
        protected ?string $publishedAfter,
        protected ?string $publishedBefore,
        protected ?string $sortColumn,
        protected string $sortDirection,
        protected bool $paginate,
        protected int $perPage,
        protected ?int $page,
        protected ?int $limit,
    ) {}

    public static function fromArray(array $payload, string $context = 'api_public'): self
    {
        $n = self::normalize($payload);
        $paginate = self::shouldPaginate($n, $context);
        $perPage = (int) ($n['per_page'] ?? ArticleFilters::defaultPerPage());
        $limit = $paginate ? null : ((int) ($n['limit'] ?? ArticleFilters::defaultLimit()));
        $direction = strtolower($n['sort_dir'] ?? 'desc');
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        return new self(
            context: $context,
            title: $n['title'] ?? null,
            q: $n['q'] ?? null,
            category: $n['category'] ?? null,
            tag: $n['tag'] ?? null,
            status: $n['status'] ?? null,
            authorId: $n['author_id'] ?? null,
            publishedAfter: $n['published_after'] ?? null,
            publishedBefore: $n['published_before'] ?? null,
            sortColumn: $n['sort'] ?? null,
            sortDirection: $direction,
            paginate: $paginate,
            perPage: max(1, $perPage),
            page: isset($n['page']) ? (int) $n['page'] : null,
            limit: $limit,
        );
    }

    public function context(): string { return $this->context; }

    public function filters(): array
    {
        return [
            'title' => $this->title,
            'q' => $this->q,
            'category' => $this->category,
            'tag' => $this->tag,
            'status' => $this->status,
            'author_id' => $this->authorId,
            'published_after' => $this->publishedAfter,
            'published_before' => $this->publishedBefore,
        ];
    }

    public function sort(): array
    {
        $default = ArticleFilters::defaultSort();
        return [$this->sortColumn ?? $default[0], $this->sortDirection ?: $default[1]];
    }

    public function paginate(): bool { return $this->paginate; }
    public function perPage(): int { return $this->perPage; }
    public function page(): ?int { return $this->page; }
    public function limit(): ?int { return $this->limit; }

    public function cacheKey(): string
    {
        return md5(json_encode([
            'filters' => $this->filters(),
            'sort' => $this->sort(),
            'paginate' => $this->paginate,
            'perPage' => $this->perPage,
            'page' => $this->page,
            'limit' => $this->limit,
        ]));
    }

    public function cacheTtl(): int { return ArticleFilters::cacheTtl(); }
    public function cacheable(): bool { return (bool) array_filter($this->filters()); }

    protected static function normalize(array $payload): array
    {
        $payload = Arr::dot($payload);
        $map = [
            'title' => ['title'],
            'q' => ['q', 'query', 'search'],
            'category' => ['category', 'category_slug'],
            'tag' => ['tag', 'tag_slug'],
            'status' => ['status'],
            'author_id' => ['author_id'],
            'published_after' => ['published_after'],
            'published_before' => ['published_before'],
            'sort' => ['sort'],
            'sort_dir' => ['sort_dir', 'order'],
            'per_page' => ['per_page'],
            'limit' => ['limit'],
            'page' => ['page'],
            'paginate' => ['paginate'],
        ];

        $n = [];
        foreach ($map as $key => $candidates) {
            foreach ($candidates as $candidate) {
                if (array_key_exists($candidate, $payload)) {
                    $n[$key] = self::cleanValue($key, $payload[$candidate]);
                    break;
                }
            }
        }
        return $n;
    }

    protected static function cleanValue(string $key, mixed $value): mixed
    {
        if (is_string($value)) $value = trim($value);
        if (in_array($key, ['q','title'], true)) return $value ? Str::of($value)->squish()->toString() : null;
        if (in_array($key, ['category','tag','status'], true)) return $value ?: null;
        if (in_array($key, ['published_after','published_before'], true)) {
            if (! $value) return null;
            try { return Carbon::parse($value)->toDateString(); } catch (Throwable) { return null; }
        }
        if (in_array($key, ['per_page','limit','page','author_id'], true)) return $value ? (int)$value : null;
        if ($key === 'paginate') return (bool)$value;
        if (in_array($key, ['sort','sort_dir'], true)) return $value ?: null;
        return $value;
    }

    protected static function shouldPaginate(array $n, string $context): bool
    {
        if (array_key_exists('per_page', $n)) return true;
        if (array_key_exists('paginate', $n) && $n['paginate'] !== null) return (bool)$n['paginate'];
        return true; // API defaults to pagination
    }
}
