<?php

namespace App\Domain\Passport\Data;

use App\Support\PassportFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Throwable;

class PassportSearchParams
{
    public function __construct(
        protected ?string $requestNumber,
        protected ?string $firstName,
        protected ?string $middleName,
        protected ?string $lastName,
        protected ?string $location,
        protected ?string $publishedAfter,
        protected ?string $publishedBefore,
        protected ?string $sortColumn,
        protected string $sortDirection,
        protected bool $paginate,
        protected int $perPage,
        protected ?int $page,
        protected ?int $limit,
    ) {
    }

    public static function fromRequest(Request $request, string $context = 'web'): self
    {
        $payload = array_merge($request->all(), $request->query());

        return self::fromArray($payload, $context);
    }

    public static function fromArray(array $payload, string $context = 'web'): self
    {
        $normalized = self::normalize($payload);

        $paginate = self::shouldPaginate($normalized, $context);
        $perPageDefault = $context === 'api' ? PassportFilters::defaultPerPage() : PassportFilters::defaultLimit();
        $perPage = (int) ($normalized['per_page'] ?? $perPageDefault);
        $limit = $paginate ? null : ((int) ($normalized['limit'] ?? ($context === 'web' ? PassportFilters::defaultLimit() : null)) ?: null);
        $direction = strtolower($normalized['sort_dir'] ?? 'desc');
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        return new self(
            requestNumber: $normalized['request_number'] ?? null,
            firstName: $normalized['first_name'] ?? null,
            middleName: $normalized['middle_name'] ?? null,
            lastName: $normalized['last_name'] ?? null,
            location: $normalized['location'] ?? null,
            publishedAfter: $normalized['published_after'] ?? null,
            publishedBefore: $normalized['published_before'] ?? null,
            sortColumn: $normalized['sort'] ?? null,
            sortDirection: $direction,
            paginate: $paginate,
            perPage: max(1, $perPage),
            page: isset($normalized['page']) ? (int) $normalized['page'] : null,
            limit: $limit,
        );
    }

    public function filters(): array
    {
        return [
            'request_number' => $this->requestNumber,
            'first_name' => $this->firstName,
            'middle_name' => $this->middleName,
            'last_name' => $this->lastName,
            'location' => $this->location,
            'published_after' => $this->publishedAfter,
            'published_before' => $this->publishedBefore,
        ];
    }

    public function sort(): array
    {
        $defaultSort = PassportFilters::defaultSort();

        return [$this->sortColumn ?? $defaultSort[0], $this->sortDirection ?: $defaultSort[1]];
    }

    public function paginate(): bool
    {
        return $this->paginate;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    public function page(): ?int
    {
        return $this->page;
    }

    public function limit(): ?int
    {
        return $this->limit;
    }

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

    public function cacheable(): bool
    {
        return (bool) array_filter($this->filters());
    }

    public function cacheTtl(): int
    {
        return PassportFilters::cacheTtl();
    }

    protected static function normalize(array $payload): array
    {
        $payload = Arr::dot($payload);

        $map = [
            'request_number' => ['request_number', 'requestNumber'],
            'first_name' => ['first_name', 'firstName'],
            'middle_name' => ['middle_name', 'middleName'],
            'last_name' => ['last_name', 'lastName'],
            'location' => ['location'],
            'published_after' => ['published_after', 'issued_after', 'dateOfPublish.from'],
            'published_before' => ['published_before', 'issued_before', 'dateOfPublish.to'],
            'sort' => ['sort', 'orderBy'],
            'sort_dir' => ['sort_dir', 'direction', 'order'],
            'per_page' => ['per_page', 'perPage'],
            'limit' => ['limit'],
            'page' => ['page'],
            'paginate' => ['paginate'],
        ];

        $normalized = [];

        foreach ($map as $key => $candidates) {
            foreach ($candidates as $candidate) {
                if (array_key_exists($candidate, $payload)) {
                    $normalized[$key] = self::cleanValue($key, $payload[$candidate]);
                    break;
                }
            }
        }

        return $normalized;
    }

    protected static function cleanValue(string $key, mixed $value): mixed
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        if (in_array($key, ['request_number'], true)) {
            return $value ? Str::upper(str_replace([' ', '-'], '', $value)) : null;
        }

        if (in_array($key, ['first_name', 'middle_name', 'last_name'], true)) {
            return $value ? Str::title($value) : null;
        }

        if (in_array($key, ['published_after', 'published_before'], true)) {
            if (! $value) {
                return null;
            }

            try {
                return Carbon::parse($value)->toDateString();
            } catch (Throwable) {
                return null;
            }
        }

        if ($key === 'location') {
            return $value ?: null;
        }

        if (in_array($key, ['per_page', 'limit', 'page'], true)) {
            return $value ? (int) $value : null;
        }

        if ($key === 'paginate') {
            return (bool) $value;
        }

        if ($key === 'sort') {
            return $value ?: null;
        }

        if ($key === 'sort_dir') {
            return $value ?: null;
        }

        return $value;
    }

    protected static function shouldPaginate(array $normalized, string $context): bool
    {
        if (array_key_exists('per_page', $normalized)) {
            return true;
        }

        if (array_key_exists('paginate', $normalized) && $normalized['paginate'] !== null) {
            return (bool) $normalized['paginate'];
        }

        return $context === 'api';
    }
}
