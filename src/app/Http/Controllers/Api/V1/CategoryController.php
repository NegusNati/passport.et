<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Article\Models\Category;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\CategoryResource;
use Illuminate\Support\Facades\Cache;

class CategoryController extends ApiController
{
    public function index()
    {
        $categories = Cache::tags(['articles', 'articles.meta'])->remember('api.v1.article.categories', 300, function () {
            return Category::query()
                ->where('is_active', true)
                ->withCount(['articles' => function ($q) {
                    $q->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now());
                }])
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'description', 'is_active']);
        });

        return $this->respond([
            'data' => CategoryResource::collection($categories)->resolve(),
            'meta' => [ 'count' => $categories->count() ],
        ]);
    }
}

