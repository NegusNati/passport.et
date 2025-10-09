<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Advertisement\Models\AdvertisementRequest;
use App\Events\AdvertisementRequestCreated;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Advertisement\StoreAdvertisementRequestRequest;
use App\Http\Resources\AdvertisementRequestResource;

class AdvertisementRequestController extends ApiController
{
    public function store(StoreAdvertisementRequestRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('advertisements/files', 'public');
            $data['file_path'] = $path;
        }

        $advertisementRequest = AdvertisementRequest::create($data);

        event(new AdvertisementRequestCreated($advertisementRequest));

        return $this->respond(new AdvertisementRequestResource($advertisementRequest), 201);
    }
}
