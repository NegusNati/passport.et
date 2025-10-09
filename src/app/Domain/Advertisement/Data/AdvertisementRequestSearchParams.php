<?php

namespace App\Domain\Advertisement\Data;

use App\Support\AdvertisementCrmFilters;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Throwable;

class AdvertisementRequestSearchParams
{
    public function __construct(
        protected ?string $fullName,
        protected ?string $companyName,
        protected ?string $phoneNumber,
        protected ?string $email,
        protected ?string $status,
        protected ?string $createdAfter,
        protected ?string $createdBefore,
        protected ?string $sortColumn,
        protected string $sortDirection,
        protected bool $paginate,
        protected int $perPage,
        protected ?int $page,
        protected ?int $limit,
    ) {}

    public static function fromArray(array $payload): self
    {
        $n = self::normalize($payload);
        $paginate = self::shouldPaginate($n);
        $perPage = (int) ($n['per_page'] ?? AdvertisementCrmFilters::defaultPerPage());
        $limit = $paginate ? null : ((int) ($n['limit'] ?? AdvertisementCrmFilters::defaultLimit()));
        $direction = strtolower($n['sort_dir'] ?? 'desc');
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        return new self(
            fullName: $n['full_name'] ?? null,
            companyName: $n['company_name'] ?? null,
            phoneNumber: $n['phone_number'] ?? null,
            email: $n['email'] ?? null,
            status: $n['status'] ?? null,
            createdAfter: $n['created_after'] ?? null,
            createdBefore: $n['created_before'] ?? null,
            sortColumn: $n['sort'] ?? null,
            sortDirection: $direction,
            paginate: $paginate,
            perPage: max(1, $perPage),
            page: isset($n['page']) ? (int) $n['page'] : null,
            limit: $limit,
        );
    }

    public function filters(): array
    {
        return [
            'full_name' => $this->fullName,
            'company_name' => $this->companyName,
            'phone_number' => $this->phoneNumber,
            'email' => $this->email,
            'status' => $this->status,
            'created_after' => $this->createdAfter,
            'created_before' => $this->createdBefore,
        ];
    }

    public function sort(): array
    {
        $default = ['created_at', 'desc'];
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

    public function cacheTtl(): int { return AdvertisementCrmFilters::cacheTtl(); }
    public function cacheable(): bool { return (bool) array_filter($this->filters()); }

    protected static function normalize(array $payload): array
    {
        $payload = Arr::dot($payload);
        $map = [
            'full_name' => ['full_name', 'fullName', 'name'],
            'company_name' => ['company_name', 'companyName', 'company'],
            'phone_number' => ['phone_number', 'phoneNumber', 'phone'],
            'email' => ['email'],
            'status' => ['status'],
            'created_after' => ['created_after', 'createdAfter'],
            'created_before' => ['created_before', 'createdBefore'],
            'sort' => ['sort'],
            'sort_dir' => ['sort_dir', 'order'],
            'per_page' => ['per_page', 'perPage'],
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
        if (in_array($key, ['full_name', 'company_name'], true)) {
            return $value ? Str::of($value)->squish()->toString() : null;
        }
        if (in_array($key, ['phone_number', 'email', 'status'], true)) {
            return $value ?: null;
        }
        if (in_array($key, ['created_after', 'created_before'], true)) {
            if (! $value) return null;
            try {
                return Carbon::parse($value)->toDateString();
            } catch (Throwable) {
                return null;
            }
        }
        if (in_array($key, ['per_page', 'limit', 'page'], true)) {
            return $value ? (int)$value : null;
        }
        if ($key === 'paginate') return (bool)$value;
        if (in_array($key, ['sort', 'sort_dir'], true)) return $value ?: null;
        return $value;
    }

    protected static function shouldPaginate(array $n): bool
    {
        if (array_key_exists('per_page', $n)) return true;
        if (array_key_exists('paginate', $n) && $n['paginate'] !== null) {
            return (bool)$n['paginate'];
        }
        return true;
    }
}
