<?php

namespace App\Actions\Advertisement;

use App\Domain\Advertisement\Data\AdvertisementSearchParams;
use App\Domain\Advertisement\Models\Advertisement;
use App\Support\CacheKeys;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SearchAdvertisementsAction
{
    public function __construct(private CacheRepository $cache) {}

    public function execute(AdvertisementSearchParams $params, bool $useCache = true): Collection|LengthAwarePaginator
    {
        $resolver = function () use ($params) {
            $query = Advertisement::query()
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

        $cacheKey = CacheKeys::adCrmSearch($params->cacheKey());

        return $this->cache
            ->tags(['ad_crm', 'ad_crm.search'])
            ->remember($cacheKey, $params->cacheTtl(), $resolver);
    }
}
