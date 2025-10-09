<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Advertisement\SearchAdvertisementRequestsAction;
use App\Domain\Advertisement\Data\AdvertisementRequestSearchParams;
use App\Domain\Advertisement\Models\AdvertisementRequest;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Advertisement\SearchAdvertisementRequestRequest;
use App\Http\Requests\Advertisement\UpdateAdvertisementRequestRequest;
use App\Http\Resources\AdvertisementRequestCollection;
use App\Http\Resources\AdvertisementRequestResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class AdvertisementRequestAdminController extends ApiController
{
    public function __construct(private SearchAdvertisementRequestsAction $search) {}

    public function index(SearchAdvertisementRequestRequest $request)
    {
        $params = AdvertisementRequestSearchParams::fromArray($request->validated());
        $results = $this->search->execute($params);

        $resource = (new AdvertisementRequestCollection($results))->additional([
            'filters' => array_filter($params->filters()),
        ]);

        return $this->respond($resource);
    }

    public function show(AdvertisementRequest $advertisementRequest)
    {
        return $this->respond(new AdvertisementRequestResource($advertisementRequest));
    }

    public function update(UpdateAdvertisementRequestRequest $request, AdvertisementRequest $advertisementRequest)
    {
        $data = $request->validated();

        if (array_key_exists('status', $data)) {
            $advertisementRequest->status = $data['status'];
        }

        if (array_key_exists('admin_notes', $data)) {
            $advertisementRequest->admin_notes = $data['admin_notes'];
        }

        if (array_key_exists('contacted_at', $data)) {
            $advertisementRequest->contacted_at = $data['contacted_at'];
        }

        if ($advertisementRequest->isDirty()) {
            $advertisementRequest->save();
        }

        Cache::tags(['advertisements'])->flush();

        return $this->respond(new AdvertisementRequestResource($advertisementRequest));
    }

    public function destroy(AdvertisementRequest $advertisementRequest)
    {
        if ($advertisementRequest->file_path && Storage::disk('public')->exists($advertisementRequest->file_path)) {
            Storage::disk('public')->delete($advertisementRequest->file_path);
        }

        $advertisementRequest->delete();

        Cache::tags(['advertisements'])->flush();

        return response()->noContent();
    }
}
