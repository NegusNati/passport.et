<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AdvertisementRequestCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        if ($this->resource instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
            return [
                'data' => AdvertisementRequestResource::collection($this->collection),
                'links' => [
                    'first' => $this->url(1),
                    'last' => $this->url($this->lastPage()),
                    'prev' => $this->previousPageUrl(),
                    'next' => $this->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $this->currentPage(),
                    'from' => $this->firstItem(),
                    'to' => $this->lastItem(),
                    'per_page' => $this->perPage(),
                    'total' => $this->total(),
                    'last_page' => $this->lastPage(),
                    'has_more' => $this->hasMorePages(),
                ],
            ];
        }

        return [
            'data' => AdvertisementRequestResource::collection($this->collection),
            'meta' => [
                'count' => $this->collection->count(),
            ],
        ];
    }
}
