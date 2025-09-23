<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PassportCollection extends ResourceCollection
{
    public $collects = PassportResource::class;

    /**
     * Transform the resource collection into an array.
     */
    public function toArray($request): array
    {
        return [
            'status' => 'not_implemented',
            'message' => 'Passport collection payload will be defined in later phases.',
        ];
    }
}
