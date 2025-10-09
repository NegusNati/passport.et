<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ArticleCollection extends ResourceCollection
{
    public $collects = ArticleResource::class;

    public function toArray($request): array
    {
        return ArticleResource::collection($this->collection)->toArray($request);
    }

    public function with($request): array
    {
        if ($this->resource instanceof LengthAwarePaginator) {
            return [
                'meta' => [
                    'has_more' => $this->resource->hasMorePages(),
                ],
            ];
        }

        return [
            'meta' => [
                'count' => $this->collection->count(),
            ],
        ];
    }
}

