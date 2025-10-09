<?php

namespace App\Observers;

use App\Domain\Advertisement\Models\AdvertisementRequest;
use Illuminate\Support\Facades\Cache;

class AdvertisementRequestObserver
{
    public function created(AdvertisementRequest $advertisementRequest): void
    {
        $this->flushCache();
    }

    public function updated(AdvertisementRequest $advertisementRequest): void
    {
        $this->flushCache();
    }

    public function deleted(AdvertisementRequest $advertisementRequest): void
    {
        $this->flushCache();
    }

    public function restored(AdvertisementRequest $advertisementRequest): void
    {
        $this->flushCache();
    }

    protected function flushCache(): void
    {
        Cache::tags(['advertisements'])->flush();
    }
}
