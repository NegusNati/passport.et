<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Advertisement\SearchAdvertisementsAction;
use App\Domain\Advertisement\Data\AdvertisementSearchParams;
use App\Domain\Advertisement\Models\Advertisement;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\AdvertisementCrm\SearchAdvertisementRequest;
use App\Http\Requests\AdvertisementCrm\StoreAdvertisementRequest;
use App\Http\Requests\AdvertisementCrm\UpdateAdvertisementRequest;
use App\Http\Resources\AdvertisementCollection;
use App\Http\Resources\AdvertisementResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class AdvertisementAdminController extends ApiController
{
    public function __construct(private SearchAdvertisementsAction $search) {}

    public function index(SearchAdvertisementRequest $request)
    {
        $params = AdvertisementSearchParams::fromArray($request->validated());
        $results = $this->search->execute($params);

        $resource = (new AdvertisementCollection($results))->additional([
            'filters' => array_filter($params->filters()),
        ]);

        return $this->respond($resource);
    }

    public function show(Advertisement $advertisement)
    {
        return $this->respond(new AdvertisementResource($advertisement));
    }

    public function store(StoreAdvertisementRequest $request)
    {
        $data = $request->validated();

        // Handle file uploads
        if ($request->hasFile('ad_desktop_asset')) {
            $path = $request->file('ad_desktop_asset')->store('advertisements/desktop', 'public');
            $data['ad_desktop_asset'] = $path;
        }

        if ($request->hasFile('ad_mobile_asset')) {
            $path = $request->file('ad_mobile_asset')->store('advertisements/mobile', 'public');
            $data['ad_mobile_asset'] = $path;
        }

        // Auto-set status to scheduled if published date is in the future
        if (isset($data['ad_published_date']) && $data['ad_published_date'] > now()->toDateString()) {
            $data['status'] = Advertisement::STATUS_SCHEDULED;
        }

        $advertisement = Advertisement::create($data);

        return $this->respond(new AdvertisementResource($advertisement), 201);
    }

    public function update(UpdateAdvertisementRequest $request, Advertisement $advertisement)
    {
        $data = $request->validated();

        // Handle file uploads
        if ($request->hasFile('ad_desktop_asset')) {
            // Delete old file if exists
            if ($advertisement->ad_desktop_asset && Storage::disk('public')->exists($advertisement->ad_desktop_asset)) {
                Storage::disk('public')->delete($advertisement->ad_desktop_asset);
            }
            $path = $request->file('ad_desktop_asset')->store('advertisements/desktop', 'public');
            $data['ad_desktop_asset'] = $path;
        }

        if ($request->hasFile('ad_mobile_asset')) {
            // Delete old file if exists
            if ($advertisement->ad_mobile_asset && Storage::disk('public')->exists($advertisement->ad_mobile_asset)) {
                Storage::disk('public')->delete($advertisement->ad_mobile_asset);
            }
            $path = $request->file('ad_mobile_asset')->store('advertisements/mobile', 'public');
            $data['ad_mobile_asset'] = $path;
        }

        // If status is being changed to active and published date is null, set it to now
        if (isset($data['status']) && $data['status'] === Advertisement::STATUS_ACTIVE && !$advertisement->ad_published_date) {
            $data['ad_published_date'] = now()->toDateString();
        }

        // Reset expiry notification flag if ending date changes
        if (isset($data['ad_ending_date']) && $data['ad_ending_date'] !== $advertisement->ad_ending_date) {
            $data['expiry_notification_sent'] = false;
        }

        $advertisement->update($data);

        return $this->respond(new AdvertisementResource($advertisement));
    }

    public function destroy(Advertisement $advertisement)
    {
        // Delete associated asset files
        if ($advertisement->ad_desktop_asset && Storage::disk('public')->exists($advertisement->ad_desktop_asset)) {
            Storage::disk('public')->delete($advertisement->ad_desktop_asset);
        }

        if ($advertisement->ad_mobile_asset && Storage::disk('public')->exists($advertisement->ad_mobile_asset)) {
            Storage::disk('public')->delete($advertisement->ad_mobile_asset);
        }

        $advertisement->delete();

        return response()->noContent();
    }

    public function restore(int $id)
    {
        $advertisement = Advertisement::withTrashed()->findOrFail($id);
        $advertisement->restore();

        return $this->respond(new AdvertisementResource($advertisement));
    }

    public function stats()
    {
        $cacheKey = 'ad_crm.stats';

        $stats = Cache::tags(['ad_crm', 'ad_crm.stats'])
            ->remember($cacheKey, 600, function () {
                $totalActive = Advertisement::active()->count();
                $expiringSoon = Advertisement::expiringSoon(3)->count();
                $expired = Advertisement::expired()->count();

                $impressionsSum = Advertisement::sum('impressions_count');
                $clicksSum = Advertisement::sum('clicks_count');
                $avgCtr = $impressionsSum > 0 ? round(($clicksSum / $impressionsSum) * 100, 2) : 0;

                $revenueThisMonth = Advertisement::where('payment_status', Advertisement::PAYMENT_PAID)
                    ->whereYear('ad_published_date', now()->year)
                    ->whereMonth('ad_published_date', now()->month)
                    ->sum('payment_amount');

                return [
                    'total_active' => $totalActive,
                    'expiring_soon' => $expiringSoon,
                    'expired_pending_renewal' => $expired,
                    'total_impressions' => $impressionsSum,
                    'total_clicks' => $clicksSum,
                    'avg_ctr' => $avgCtr,
                    'revenue_this_month' => round($revenueThisMonth, 2),
                ];
            });

        return $this->respond(['data' => $stats]);
    }
}
