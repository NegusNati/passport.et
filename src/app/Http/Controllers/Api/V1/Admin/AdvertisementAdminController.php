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
                $statusCounts = Advertisement::query()
                    ->selectRaw('status, COUNT(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status');

                $paymentCounts = Advertisement::query()
                    ->selectRaw('payment_status, COUNT(*) as total')
                    ->groupBy('payment_status')
                    ->pluck('total', 'payment_status');

                $totalActive = Advertisement::active()->count();
                $expiringSoon = Advertisement::expiringSoon(3)->count();
                $expired = Advertisement::expired()->count();

                $totalAdvertisements = Advertisement::count();
                $impressionsSum = (int) Advertisement::sum('impressions_count');
                $clicksSum = (int) Advertisement::sum('clicks_count');
                $avgCtr = $impressionsSum > 0 ? round(($clicksSum / $impressionsSum) * 100, 2) : 0;

                $paidAdvertisements = Advertisement::where('payment_status', Advertisement::PAYMENT_PAID);
                $totalRevenue = (float) (clone $paidAdvertisements)->sum('payment_amount');
                $revenueThisMonth = (float) (clone $paidAdvertisements)
                    ->whereYear('ad_published_date', now()->year)
                    ->whereMonth('ad_published_date', now()->month)
                    ->sum('payment_amount');

                $revenueLast30Days = (float) (clone $paidAdvertisements)
                    ->whereDate('ad_published_date', '>=', now()->subDays(30)->toDateString())
                    ->sum('payment_amount');

                $topPerformers = Advertisement::query()
                    ->select([
                        'id',
                        'ad_title',
                        'slot_code',
                        'status',
                        'impressions_count',
                        'clicks_count',
                        'payment_amount',
                    ])
                    ->orderByDesc('clicks_count')
                    ->orderByDesc('impressions_count')
                    ->limit(5)
                    ->get()
                    ->map(fn (Advertisement $advertisement) => [
                        'id' => (int) $advertisement->id,
                        'ad_title' => $advertisement->ad_title,
                        'slot_code' => $advertisement->slot_code,
                        'status' => $advertisement->status,
                        'impressions_count' => (int) $advertisement->impressions_count,
                        'clicks_count' => (int) $advertisement->clicks_count,
                        'ctr' => $advertisement->impressions_count > 0
                            ? round(($advertisement->clicks_count / $advertisement->impressions_count) * 100, 2)
                            : 0.0,
                        'payment_amount' => (float) $advertisement->payment_amount,
                    ])
                    ->values();

                return [
                    'total_advertisements' => (int) $totalAdvertisements,
                    'total_active' => (int) $totalActive,
                    'total_draft' => (int) ($statusCounts[Advertisement::STATUS_DRAFT] ?? 0),
                    'total_scheduled' => (int) ($statusCounts[Advertisement::STATUS_SCHEDULED] ?? 0),
                    'total_paused' => (int) ($statusCounts[Advertisement::STATUS_PAUSED] ?? 0),
                    'expiring_soon' => (int) $expiringSoon,
                    'expired_pending_renewal' => (int) $expired,
                    'paid_advertisements' => (int) ($paymentCounts[Advertisement::PAYMENT_PAID] ?? 0),
                    'pending_payment' => (int) ($paymentCounts[Advertisement::PAYMENT_PENDING] ?? 0),
                    'total_impressions' => $impressionsSum,
                    'total_clicks' => $clicksSum,
                    'avg_ctr' => (float) $avgCtr,
                    'total_revenue' => round($totalRevenue, 2),
                    'revenue_this_month' => round($revenueThisMonth, 2),
                    'revenue_last_30_days' => round($revenueLast30Days, 2),
                    'top_performers' => $topPerformers,
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
