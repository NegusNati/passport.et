<?php

namespace App\Domain\Passport\Models;

use App\Domain\Passport\Data\PassportSearchParams;
use App\Support\PassportFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Passport extends Model
{
    use HasFactory;

    protected $table = 'p_d_f_to_s_q_lites';

    protected $fillable = [
        'no',
        'firstName',
        'middleName',
        'lastName',
        'requestNumber',
        'location',
        'dateOfPublish',
    ];

    protected $casts = [
        'dateOfPublish' => 'date',
    ];

    /**
     * Scope a query with sanitized filters.
     */
    public function scopeFilter(Builder $query, PassportSearchParams $params): Builder
    {
        $filters = $params->filters();

        if ($filters['request_number']) {
            $query->where('requestNumber', 'like', $this->prefixPattern($filters['request_number']));
        }

        $this->applyNameFilters($query, $filters);

        if ($filters['location']) {
            $query->where('location', $filters['location']);
        }

        if ($filters['published_after']) {
            $query->whereDate('dateOfPublish', '>=', $filters['published_after']);
        }

        if ($filters['published_before']) {
            $query->whereDate('dateOfPublish', '<=', $filters['published_before']);
        }

        return $query;
    }

    /**
     * Scope a query with default or requested sorting.
     */
    public function scopeSort(Builder $query, PassportSearchParams $params): Builder
    {
        [$column, $direction] = $params->sort();

        if (! in_array($column, PassportFilters::sortableColumns(), true)) {
            [$column, $direction] = PassportFilters::defaultSort();
        }

        return $query->orderBy($column, $direction);
    }

    /**
     * Scope a query to limit results when pagination is disabled.
     */
    public function scopeLimitForSearch(Builder $query, PassportSearchParams $params): Builder
    {
        $limit = $params->limit() ?? PassportFilters::defaultLimit();

        return $query->limit($limit);
    }

    protected function applyNameFilters(Builder $query, array $filters): void
    {
        $nameParts = [
            'first' => $filters['first_name'] ?? null,
            'middle' => $filters['middle_name'] ?? null,
            'last' => $filters['last_name'] ?? null,
        ];

        $hasExplicit = false;

        foreach ($nameParts as $key => $value) {
            if (! $value) {
                continue;
            }

            $hasExplicit = true;

            $column = match ($key) {
                'first' => 'firstName',
                'middle' => 'middleName',
                default => 'lastName',
            };

            $query->where($column, 'like', $this->prefixPattern($value));
        }

        if (! $hasExplicit && isset($filters['name']) && $filters['name']) {
            $tokens = preg_split('/\s+/u', $filters['name'], -1, PREG_SPLIT_NO_EMPTY) ?: [];

            foreach ($tokens as $token) {
                $token = Str::title($token);

                $query->where(function (Builder $tokenQuery) use ($token) {
                    $pattern = $this->prefixPattern($token);

                    $tokenQuery
                        ->where('firstName', 'like', $pattern)
                        ->orWhere('middleName', 'like', $pattern)
                        ->orWhere('lastName', 'like', $pattern);
                });
            }
        }
    }

    protected function prefixPattern(string $value): string
    {
        $escaped = addcslashes($value, '%_');

        return $escaped.'%';
    }

    /**
     * Normalize attributes for caching keys.
     */
    public function getCacheIdentifier(): string
    {
        return Str::slug(sprintf('%s-%s', $this->requestNumber, optional($this->dateOfPublish)->format('Ymd')));
    }

    protected static function newFactory()
    {
        return \Database\Factories\PassportFactory::new();
    }
}
