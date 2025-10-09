<?php

namespace App\Domain\Advertisement\Data;

use App\Support\AdvertisementCrmFilters;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Throwable;

class AdvertisementSearchParams
{
    public function __construct(
        protected ?string $adTitle,
        protected ?string $adSlotNumber,
        protected ?string $clientName,
        protected ?string $status,
        protected ?string $paymentStatus,
        protected ?string $packageType,
        protected ?string $publishedAfter,
        protected ?string $publishedBefore,
        protected ?string $endingAfter,
        protected ?string $endingBefore,
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
            adTitle: $n['ad_title'] ?? null,
            adSlotNumber: $n['ad_slot_number'] ?? null,
            clientName: $n['client_name'] ?? null,
            status: $n['status'] ?? null,
            paymentStatus: $n['payment_status'] ?? null,
            packageType: $n['package_type'] ?? null,
            publishedAfter: $n['published_after'] ?? null,
            publishedBefore: $n['published_before'] ?? null,
            endingAfter: $n['ending_after'] ?? null,
            endingBefore: $n['ending_before'] ?? null,
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
            'ad_title' => $this->adTitle,
            'ad_slot_number' => $this->adSlotNumber,
            'client_name' => $this->clientName,
            'status' => $this->status,
            'payment_status' => $this->paymentStatus,
            'package_type' => $this->packageType,
            'published_after' => $this->publishedAfter,
            'published_before' => $this->publishedBefore,
            'ending_after' => $this->endingAfter,
            'ending_before' => $this->endingBefore,
        ];
    }

    public function sort(): array
    {
        $default = AdvertisementCrmFilters::defaultSort();
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
            'ad_title' => ['ad_title', 'adTitle', 'title'],
            'ad_slot_number' => ['ad_slot_number', 'adSlotNumber', 'slot_number', 'slot'],
            'client_name' => ['client_name', 'clientName', 'client'],
            'status' => ['status'],
            'payment_status' => ['payment_status', 'paymentStatus'],
            'package_type' => ['package_type', 'packageType', 'package'],
            'published_after' => ['published_after', 'publishedAfter'],
            'published_before' => ['published_before', 'publishedBefore'],
            'ending_after' => ['ending_after', 'endingAfter'],
            'ending_before' => ['ending_before', 'endingBefore'],
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
        if (in_array($key, ['ad_title','client_name'], true)) return $value ? Str::of($value)->squish()->toString() : null;
        if (in_array($key, ['ad_slot_number','status','payment_status','package_type'], true)) return $value ?: null;
        if (in_array($key, ['published_after','published_before','ending_after','ending_before'], true)) {
            if (! $value) return null;
            try { return Carbon::parse($value)->toDateString(); } catch (Throwable) { return null; }
        }
        if (in_array($key, ['per_page','limit','page'], true)) return $value ? (int)$value : null;
        if ($key === 'paginate') return (bool)$value;
        if (in_array($key, ['sort','sort_dir'], true)) return $value ?: null;
        return $value;
    }

    protected static function shouldPaginate(array $n): bool
    {
        if (array_key_exists('per_page', $n)) return true;
        if (array_key_exists('paginate', $n) && $n['paginate'] !== null) return (bool)$n['paginate'];
        return true;
    }
}
