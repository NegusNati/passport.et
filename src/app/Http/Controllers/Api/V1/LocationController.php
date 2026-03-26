<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Passport\Models\Passport;
use App\Http\Controllers\Api\ApiController;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;

class LocationController extends ApiController
{
    public function index()
    {
        $locations = Cache::tags(['passports', 'passports.locations'])
            ->remember(CacheKeys::locationsList(), 300, function () {
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
