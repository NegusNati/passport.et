<?php

namespace App\Actions\Advertisement;

use App\Domain\Advertisement\Data\AdvertisementRequestSearchParams;
use App\Domain\Advertisement\Models\AdvertisementRequest;
use App\Support\CacheKeys;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SearchAdvertisementRequestsAction
{
    public function __construct(private CacheRepository $cache) {}

    public function execute(AdvertisementRequestSearchParams $params, bool $useCache = true): Collection|LengthAwarePaginator
    {
        $resolver = function () use ($params) {
            $query = AdvertisementRequest::query()
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

        $cacheKey = CacheKeys::advertisementSearch($params->cacheKey());

        return $this->cache
            ->tags(['advertisements', 'advertisements.search'])
            ->remember($cacheKey, $params->cacheTtl(), $resolver);
    }
}
