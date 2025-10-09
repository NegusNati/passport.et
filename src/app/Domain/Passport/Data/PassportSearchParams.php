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
        protected ?string $fullName,
        protected ?string $rawQuery,
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
        $rawQuery = $normalized['query'] ?? null;
        $normalized = self::hydrateGeneralQuery($normalized);
        $normalized = self::applyPageSizeAlias($normalized);
        [$firstName, $middleName, $lastName, $fullName] = self::resolveNameParts($normalized);

        $paginate = self::shouldPaginate($normalized, $context);
        $perPageDefault = $context === 'api' ? PassportFilters::defaultPerPage() : PassportFilters::defaultLimit();
        $perPage = (int) ($normalized['per_page'] ?? $perPageDefault);
        $limit = $paginate ? null : ((int) ($normalized['limit'] ?? ($context === 'web' ? PassportFilters::defaultLimit() : null)) ?: null);
        $direction = strtolower($normalized['sort_dir'] ?? 'desc');
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        return new self(
            requestNumber: $normalized['request_number'] ?? null,
            firstName: $firstName,
            middleName: $middleName,
            lastName: $lastName,
            location: $normalized['location'] ?? null,
            publishedAfter: $normalized['published_after'] ?? null,
            publishedBefore: $normalized['published_before'] ?? null,
            sortColumn: $normalized['sort'] ?? null,
            sortDirection: $direction,
            paginate: $paginate,
            perPage: max(1, $perPage),
            page: isset($normalized['page']) ? (int) $normalized['page'] : null,
            limit: $limit,
            fullName: $fullName,
            rawQuery: $rawQuery ? Str::squish($rawQuery) : null,
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
            'name' => $this->fullName,
            'query' => $this->rawQuery,
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
            'name' => ['name', 'full_name', 'fullName'],
            'query' => ['query', 'q', 'term'],
            'location' => ['location'],
            'published_after' => ['published_after', 'issued_after', 'dateOfPublish.from'],
            'published_before' => ['published_before', 'issued_before', 'dateOfPublish.to'],
            'sort' => ['sort', 'orderBy'],
            'sort_dir' => ['sort_dir', 'direction', 'order'],
            'per_page' => ['per_page', 'perPage'],
            'page_size' => ['page_size', 'pageSize'],
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

        if (in_array($key, ['name', 'query'], true)) {
            return $value ? Str::squish($value) : null;
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

        if (in_array($key, ['per_page', 'page_size', 'limit', 'page'], true)) {
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

    protected static function applyPageSizeAlias(array $normalized): array
    {
        if (isset($normalized['page_size']) && ! isset($normalized['per_page'])) {
            $normalized['per_page'] = $normalized['page_size'];
        }

        return $normalized;
    }

    protected static function hydrateGeneralQuery(array $normalized): array
    {
        if (! array_key_exists('query', $normalized) || $normalized['query'] === null) {
            return $normalized;
        }

        $query = $normalized['query'];
        $candidate = self::cleanValue('request_number', $query);
        $hasDigits = $candidate && preg_match('/\d/u', $candidate);

        if (! isset($normalized['request_number']) && $candidate && $hasDigits && strlen($candidate) >= 3) {
            $normalized['request_number'] = $candidate;
        } elseif (! isset($normalized['name'])) {
            $normalized['name'] = $query;
        }

        return $normalized;
    }

    /**
     * Resolve normalized name parts from discrete or composite inputs.
     */
    protected static function resolveNameParts(array &$normalized): array
    {
        $first = $normalized['first_name'] ?? null;
        $middle = $normalized['middle_name'] ?? null;
        $last = $normalized['last_name'] ?? null;

        if (isset($normalized['name']) && $normalized['name']) {
            $parts = self::splitCompositeName($normalized['name']);

            $first ??= $parts['first'];

            if (! $middle && $parts['middle']) {
                $middle = $parts['middle'];
            }

            $last ??= $parts['last'];
        }

        $fullName = self::buildFullName($first, $middle, $last);

        unset($normalized['name']);

        return [$first, $middle, $last, $fullName];
    }

    /**
     * Break a composite name string into first/middle/last segments.
     */
    protected static function splitCompositeName(string $value): array
    {
        $cleaned = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $value);
        $cleaned = $cleaned ? Str::squish($cleaned) : '';

        if ($cleaned === '') {
            return ['first' => null, 'middle' => null, 'last' => null];
        }

        $parts = preg_split('/\s+/u', $cleaned, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($parts) === 1) {
            return [
                'first' => self::normalizeNameComponent($parts[0]),
                'middle' => null,
                'last' => null,
            ];
        }

        $first = array_shift($parts);
        $last = array_pop($parts);
        $middle = $parts ? implode(' ', $parts) : null;

        return [
            'first' => self::normalizeNameComponent($first),
            'middle' => $middle ? self::normalizeNameComponent($middle) : null,
            'last' => $last ? self::normalizeNameComponent($last) : null,
        ];
    }

    protected static function buildFullName(?string ...$parts): ?string
    {
        $parts = array_values(array_filter($parts));

        return $parts ? implode(' ', $parts) : null;
    }

    protected static function normalizeNameComponent(?string $value): ?string
    {
        return $value ? Str::title(Str::lower($value)) : null;
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
