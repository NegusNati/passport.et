<?php

namespace App\Events;

use App\Domain\Advertisement\Models\AdvertisementRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdvertisementRequestCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public AdvertisementRequest $advertisementRequest)
    {
    }
}
