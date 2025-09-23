<?php

namespace App\Actions\Passport;

use App\Domain\Passport\Data\PassportSearchParams;
use App\Domain\Passport\Models\Passport;
use App\Support\CacheKeys;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SearchPassportsAction
{
    public function __construct(private CacheRepository $cache)
    {
    }

    /**
     * Execute the passport search with shared filters.
     */
    public function execute(PassportSearchParams $params, bool $useCache = true): Collection|LengthAwarePaginator
    {
        $resolver = function () use ($params) {
            $query = Passport::query()
                ->filter($params)
                ->sort($params);

            if ($params->paginate()) {
                return $query->paginate(
                    perPage: $params->perPage(),
                    page: $params->page() ?? 1,
                )->appends($params->filters());
            }

            return $query->limitForSearch($params)->get();
        };

        if (! $useCache || ! $params->cacheable()) {
            return $resolver();
        }

        $cacheKey = CacheKeys::passportSearch($params->cacheKey());

        return $this->cache
            ->tags(['passports', 'passports.search'])
            ->remember($cacheKey, $params->cacheTtl(), $resolver);
    }
}
