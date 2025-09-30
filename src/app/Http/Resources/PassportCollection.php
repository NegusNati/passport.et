<?php

namespace App\Http\Resources;

use App\Support\PassportFilters;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PassportCollection extends ResourceCollection
{
    public $collects = PassportResource::class;

    /**
     * Transform the resource collection into an array.
     */
    public function toArray($request): array
    {
        return PassportResource::collection($this->collection)->toArray($request);
    }

    public function with($request): array
    {
        $meta = [
            'page_size_options' => PassportFilters::pageSizeOptions(),
        ];

        if ($this->resource instanceof LengthAwarePaginator) {
            $meta['has_more'] = $this->resource->hasMorePages();
            $meta['page_size'] = $this->resource->perPage();
        } else {
            $meta['count'] = $this->collection->count();
            $meta['page_size'] = null;
        }

        return [
            'meta' => $meta,
        ];
    }
}
