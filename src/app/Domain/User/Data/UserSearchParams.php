<?php

namespace App\Domain\User\Data;

use App\Support\UserFilters;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Throwable;

class UserSearchParams
{
    public function __construct(
        protected ?string $search,
        protected ?string $email,
        protected ?string $phoneNumber,
        protected ?string $role,
        protected ?string $plan,
        protected ?bool $isAdmin,
        protected ?bool $emailVerified,
        protected ?string $createdFrom,
        protected ?string $createdTo,
        protected string $sortColumn,
        protected string $sortDirection,
        protected int $perPage,
        protected ?int $page,
    ) {
    }

    public static function fromArray(array $payload): self
    {
        $normalized = self::normalize($payload);

        $sortColumn = $normalized['sort'] ?? UserFilters::defaultSort()[0];
        if (! in_array($sortColumn, UserFilters::sortableColumns(), true)) {
            $sortColumn = UserFilters::defaultSort()[0];
        }

        $direction = strtolower($normalized['direction'] ?? UserFilters::defaultSort()[1]);
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : UserFilters::defaultSort()[1];

        $perPage = (int) ($normalized['per_page'] ?? UserFilters::defaultPerPage());
        if ($perPage < 1) {
            $perPage = 1;
        }
        if ($perPage > 100) {
            $perPage = 100;
        }

        return new self(
            search: $normalized['search'] ?? null,
            email: $normalized['email'] ?? null,
            phoneNumber: $normalized['phone_number'] ?? null,
            role: $normalized['role'] ?? null,
            plan: $normalized['plan'] ?? null,
            isAdmin: $normalized['is_admin'] ?? null,
            emailVerified: $normalized['email_verified'] ?? null,
            createdFrom: $normalized['created_from'] ?? null,
            createdTo: $normalized['created_to'] ?? null,
            sortColumn: $sortColumn,
            sortDirection: $direction,
            perPage: $perPage,
            page: isset($normalized['page']) ? (int) $normalized['page'] : null,
        );
    }

    public function filters(): array
    {
        return [
            'search' => $this->search,
            'email' => $this->email,
            'phone_number' => $this->phoneNumber,
            'role' => $this->role,
            'plan' => $this->plan,
            'is_admin' => $this->isAdmin,
            'email_verified' => $this->emailVerified,
            'created_from' => $this->createdFrom,
            'created_to' => $this->createdTo,
        ];
    }

    public function sort(): array
    {
        return [$this->sortColumn, $this->sortDirection];
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    public function page(): ?int
    {
        return $this->page;
    }

    protected static function normalize(array $payload): array
    {
        $payload = Arr::dot($payload);

        $map = [
            'search' => ['search', 'q', 'query'],
            'email' => ['email'],
            'phone_number' => ['phone_number', 'phoneNumber'],
            'role' => ['role'],
            'plan' => ['plan', 'plan_type', 'subscription.plan'],
            'is_admin' => ['is_admin', 'admin'],
            'email_verified' => ['email_verified', 'verified'],
            'created_from' => ['created_from', 'created_after', 'createdAt.from'],
            'created_to' => ['created_to', 'created_before', 'createdAt.to'],
            'sort' => ['sort', 'orderBy'],
            'direction' => ['direction', 'order', 'sort_dir'],
            'per_page' => ['per_page', 'perPage'],
            'page' => ['page'],
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

        if (in_array($key, ['search', 'email', 'phone_number', 'role', 'plan'], true)) {
            return $value !== '' ? Str::squish($value) : null;
        }

        if (in_array($key, ['is_admin', 'email_verified'], true)) {
            if (is_bool($value)) {
                return $value;
            }
            $value = strtolower((string) $value);
            return match ($value) {
                '1', 'true', 'yes', 'on' => true,
                '0', 'false', 'no', 'off' => false,
                default => null,
            };
        }

        if (in_array($key, ['created_from', 'created_to'], true)) {
            if (! $value) {
                return null;
            }

            try {
                return Carbon::parse($value)->toDateString();
            } catch (Throwable) {
                return null;
            }
        }

        if ($key === 'per_page') {
            return (int) $value;
        }

        if ($key === 'page') {
            return (int) $value;
        }

        if (in_array($key, ['sort', 'direction'], true)) {
            return $value ?: null;
        }

        return $value;
    }
}
