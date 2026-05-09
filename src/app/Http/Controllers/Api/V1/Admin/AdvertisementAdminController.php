<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Advertisement\SearchAdvertisementsAction;
use App\Domain\Advertisement\Data\AdvertisementSearchParams;
use App\Domain\Advertisement\Models\AdSlot;
use App\Domain\Advertisement\Models\Advertisement;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\AdvertisementCrm\SearchAdvertisementRequest;
use App\Http\Requests\AdvertisementCrm\StoreAdvertisementRequest;
use App\Http\Requests\AdvertisementCrm\UpdateAdvertisementRequest;
use App\Http\Resources\AdvertisementCollection;
use App\Http\Resources\AdvertisementResource;
use Illuminate\Http\UploadedFile;
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

    public function slots()
    {
        $slots = AdSlot::query()
            ->where('is_active', true)
            ->orderBy('page_context')
            ->orderBy('code')
            ->get()
            ->map(fn (AdSlot $slot) => [
                'id' => $slot->id,
                'code' => $slot->code,
                'name' => $slot->name,
                'page_context' => $slot->page_context,
                'format' => $slot->format,
                'desktop_width' => $slot->desktop_width,
                'desktop_height' => $slot->desktop_height,
                'mobile_width' => $slot->mobile_width,
                'mobile_height' => $slot->mobile_height,
                'is_active' => $slot->is_active,
            ]);

        return $this->respond(['data' => $slots]);
    }

    public function store(StoreAdvertisementRequest $request)
    {
        $data = $request->validated();

        $this->storeUploadedAsset($request, $data, 'ad_desktop_asset', 'advertisements/desktop', 'desktop');
        $this->storeUploadedAsset($request, $data, 'ad_desktop_dark_asset', 'advertisements/desktop-dark');
        $this->storeUploadedAsset($request, $data, 'ad_mobile_asset', 'advertisements/mobile', 'mobile');
        $this->storeUploadedAsset($request, $data, 'ad_mobile_dark_asset', 'advertisements/mobile-dark');

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

        $this->replaceUploadedAsset($request, $advertisement, $data, 'ad_desktop_asset', 'advertisements/desktop', 'desktop');
        $this->replaceUploadedAsset($request, $advertisement, $data, 'ad_desktop_dark_asset', 'advertisements/desktop-dark');
        $this->replaceUploadedAsset($request, $advertisement, $data, 'ad_mobile_asset', 'advertisements/mobile', 'mobile');
        $this->replaceUploadedAsset($request, $advertisement, $data, 'ad_mobile_dark_asset', 'advertisements/mobile-dark');

        unset(
            $data['remove_ad_desktop_asset'],
            $data['remove_ad_desktop_dark_asset'],
            $data['remove_ad_mobile_asset'],
            $data['remove_ad_mobile_dark_asset'],
        );

        // If status is being changed to active and published date is null, set it to now
        if (isset($data['status']) && $data['status'] === Advertisement::STATUS_ACTIVE && ! $advertisement->ad_published_date) {
            $data['ad_published_date'] = now()->toDateString();
        }

        if ($request->boolean('remove_ad_desktop_asset') && ! $request->hasFile('ad_desktop_asset')) {
            $this->deleteStoredAsset($advertisement->ad_desktop_asset);
            $data['ad_desktop_asset'] = null;
            $data['desktop_width'] = null;
            $data['desktop_height'] = null;
        }

        if ($request->boolean('remove_ad_desktop_dark_asset') && ! $request->hasFile('ad_desktop_dark_asset')) {
            $this->deleteStoredAsset($advertisement->ad_desktop_dark_asset);
            $data['ad_desktop_dark_asset'] = null;
        }

        if ($request->boolean('remove_ad_mobile_asset') && ! $request->hasFile('ad_mobile_asset')) {
            $this->deleteStoredAsset($advertisement->ad_mobile_asset);
            $data['ad_mobile_asset'] = null;
            $data['mobile_width'] = null;
            $data['mobile_height'] = null;
        }

        if ($request->boolean('remove_ad_mobile_dark_asset') && ! $request->hasFile('ad_mobile_dark_asset')) {
            $this->deleteStoredAsset($advertisement->ad_mobile_dark_asset);
            $data['ad_mobile_dark_asset'] = null;
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
        $this->deleteStoredAsset($advertisement->ad_desktop_asset);
        $this->deleteStoredAsset($advertisement->ad_desktop_dark_asset);
        $this->deleteStoredAsset($advertisement->ad_mobile_asset);
        $this->deleteStoredAsset($advertisement->ad_mobile_dark_asset);

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

    protected function imageDimensions(UploadedFile $file, string $prefix): array
    {
        $size = @getimagesize($file->getRealPath());

        if (! is_array($size)) {
            return [];
        }

        return [
            "{$prefix}_width" => $size[0],
            "{$prefix}_height" => $size[1],
        ];
    }

    protected function storeUploadedAsset(
        StoreAdvertisementRequest|UpdateAdvertisementRequest $request,
        array &$data,
        string $field,
        string $directory,
        ?string $dimensionPrefix = null,
    ): void {
        if (! $request->hasFile($field)) {
            return;
        }

        $file = $request->file($field);
        $data[$field] = $file->store($directory, 'public');

        if ($dimensionPrefix) {
            $data = array_merge($data, $this->imageDimensions($file, $dimensionPrefix));
        }
    }

    protected function replaceUploadedAsset(
        UpdateAdvertisementRequest $request,
        Advertisement $advertisement,
        array &$data,
        string $field,
        string $directory,
        ?string $dimensionPrefix = null,
    ): void {
        if (! $request->hasFile($field)) {
            return;
        }

        $this->deleteStoredAsset($advertisement->{$field});
        $this->storeUploadedAsset($request, $data, $field, $directory, $dimensionPrefix);
    }

    protected function deleteStoredAsset(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
