<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Advertisement\Models\Advertisement;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\AdvertisementCollection;
use App\Jobs\IncrementAdClickJob;
use App\Jobs\IncrementAdImpressionJob;
use App\Support\CacheKeys;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdvertisementController extends ApiController
{
    public function active(Request $request)
    {
        $slotNumber = $request->query('slot_number');

        $cacheKey = $slotNumber 
            ? CacheKeys::adCrmBySlot($slotNumber)
            : CacheKeys::adCrmActiveSlots();

        $advertisements = Cache::tags(['ad_crm', 'ad_crm.active'])
            ->remember($cacheKey, 300, function () use ($slotNumber) {
                $query = Advertisement::active()
                    ->orderBy('priority', 'desc')
                    ->orderBy('ad_published_date', 'asc');

                if ($slotNumber) {
                    $query->bySlot($slotNumber);
                }

                return $query->get();
            });

        return $this->respond(new AdvertisementCollection($advertisements));
    }

    public function impression(Request $request, Advertisement $advertisement)
    {
        $sessionId = $request->input('session_id');

        // Deduplicate impressions using Redis
        if ($sessionId) {
            $dedupeKey = "ad_impression:{$advertisement->id}:{$sessionId}";
            
            if (Cache::has($dedupeKey)) {
                return response()->noContent();
            }

            Cache::put($dedupeKey, true, 10); // 10 second window
        }

        // Queue the increment job to avoid blocking
        IncrementAdImpressionJob::dispatch($advertisement->id);

        return response()->noContent();
    }

    public function click(Request $request, Advertisement $advertisement)
    {
        $sessionId = $request->input('session_id');

        // Deduplicate clicks using Redis
        if ($sessionId) {
            $dedupeKey = "ad_click:{$advertisement->id}:{$sessionId}";
            
            if (Cache::has($dedupeKey)) {
                return response()->noContent();
            }

            Cache::put($dedupeKey, true, 60); // 1 minute window
        }

        // Queue the increment job
        IncrementAdClickJob::dispatch($advertisement->id);

        return response()->noContent();
    }
}
