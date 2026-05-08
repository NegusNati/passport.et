<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Advertisement\Models\Advertisement;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\AdvertisementCollection;
use App\Http\Resources\AdvertisementPublicResource;
use App\Jobs\IncrementAdClickJob;
use App\Jobs\IncrementAdImpressionJob;
use App\Support\CacheKeys;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdvertisementController extends ApiController
{
    public function slots(Request $request)
    {
        $codes = $this->requestedSlotCodes($request);

        if ($codes === []) {
            return $this->respond(['data' => []]);
        }

        $cacheKey = CacheKeys::adPublicSlots($codes);

        $advertisements = Cache::tags(['ad_crm', 'ad_crm.active'])
            ->remember($cacheKey, 300, fn () => $this->activeAdsForSlots($codes));

        $data = [];

        foreach ($codes as $code) {
            $advertisement = $advertisements->first(
                fn (Advertisement $ad) => $ad->publicSlotCode() === $code
            );

            $data[$code] = $advertisement
                ? (new AdvertisementPublicResource($advertisement))->resolve($request)
                : null;
        }

        return $this->respond(['data' => $data]);
    }

    public function slot(Request $request, string $code)
    {
        $advertisements = $this->activeAdsForSlots([$code]);
        $advertisement = $advertisements->first();

        return $this->respond([
            'data' => $advertisement
                ? (new AdvertisementPublicResource($advertisement))->resolve($request)
                : null,
        ]);
    }

    public function placement(Request $request)
    {
        $placement = $request->query('placement') ?: $request->query('slot_number');

        if (! is_string($placement) || trim($placement) === '') {
            return $this->respond(['data' => null]);
        }

        return $this->slot($request, trim($placement));
    }

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

    public function impressionByPayload(Request $request)
    {
        $advertisement = Advertisement::findOrFail($request->input('ad_id'));

        return $this->impression($request, $advertisement);
    }

    public function clickByPayload(Request $request)
    {
        $advertisement = Advertisement::findOrFail($request->input('ad_id'));

        return $this->click($request, $advertisement);
    }

    protected function requestedSlotCodes(Request $request): array
    {
        $codes = $request->query('codes', []);

        if (is_string($codes)) {
            $codes = explode(',', $codes);
        }

        if (! is_array($codes)) {
            $codes = [];
        }

        foreach (['code', 'placement', 'slot_number'] as $key) {
            $value = $request->query($key);

            if (is_string($value) && trim($value) !== '') {
                $codes[] = $value;
            }
        }

        return collect($codes)
            ->filter(fn ($code) => is_string($code) && trim($code) !== '')
            ->map(fn (string $code) => trim($code))
            ->unique()
            ->values()
            ->all();
    }

    protected function activeAdsForSlots(array $codes)
    {
        return Advertisement::query()
            ->with('adSlot')
            ->active()
            ->where(function ($query) use ($codes) {
                $query->whereIn('slot_code', $codes)
                    ->orWhereIn('ad_slot_number', $codes);
            })
            ->orderBy('priority', 'desc')
            ->orderBy('ad_published_date', 'asc')
            ->get()
            ->unique(fn (Advertisement $ad) => $ad->publicSlotCode())
            ->values();
    }
}
