<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Passport\Models\Passport;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Support\Facades\Cache;

class LocationController extends ApiController
{
    public function index()
    {
        $locations = Cache::remember('api.v1.locations', 300, function () {
            return Passport::query()
                ->select('location')
                ->whereNotNull('location')
                ->distinct()
                ->orderBy('location')
                ->pluck('location')
                ->values();
        });

        return $this->respond([
            'data' => $locations,
            'meta' => [
                'count' => $locations->count(),
            ],
        ]);
    }
}
