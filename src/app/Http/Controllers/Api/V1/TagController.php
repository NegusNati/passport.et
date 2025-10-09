<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Article\Models\Tag;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\TagResource;
use Illuminate\Support\Facades\Cache;

class TagController extends ApiController
{
    public function index()
    {
        $tags = Cache::tags(['articles', 'articles.meta'])->remember('api.v1.article.tags', 300, function () {
            return Tag::query()
                ->where('is_active', true)
                ->withCount(['articles' => function ($q) {
                    $q->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now());
                }])
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'description', 'is_active']);
        });

        return $this->respond([
            'data' => TagResource::collection($tags)->resolve(),
            'meta' => [ 'count' => $tags->count() ],
        ]);
    }
}

