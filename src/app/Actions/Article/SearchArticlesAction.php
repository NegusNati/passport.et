<?php

namespace App\Actions\Article;

use App\Domain\Article\Data\ArticleSearchParams;
use App\Domain\Article\Models\Article;
use App\Support\CacheKeys;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SearchArticlesAction
{
    public function __construct(private CacheRepository $cache) {}

    public function execute(ArticleSearchParams $params, bool $useCache = true): Collection|LengthAwarePaginator
    {
        $resolver = function () use ($params) {
            $query = Article::query()
                ->with(['tags:id,slug,name', 'categories:id,slug,name'])
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

        $cacheKey = CacheKeys::articleSearch($params->cacheKey());

        return $this->cache
            ->tags(['articles', 'articles.search'])
            ->remember($cacheKey, $params->cacheTtl(), $resolver);
    }
}

