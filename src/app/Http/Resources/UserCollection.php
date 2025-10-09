<?php

namespace App\Http\Resources;

use App\Support\UserFilters;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    public $collects = UserResource::class;

    public function toArray($request): array
    {
        return UserResource::collection($this->collection)->toArray($request);
    }

    public function with($request): array
    {
        $meta = [
            'page_size_options' => UserFilters::perPageOptions(),
        ];

        if ($this->resource instanceof LengthAwarePaginator) {
            $meta['has_more'] = $this->resource->hasMorePages();
            $meta['page_size'] = $this->resource->perPage();
            $meta['total'] = $this->resource->total();
        } else {
            $meta['count'] = $this->collection->count();
        }

        return [
            'meta' => $meta,
        ];
    }
}
