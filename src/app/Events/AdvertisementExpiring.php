<?php

namespace App\Events;

use App\Domain\Advertisement\Models\Advertisement;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdvertisementExpiring
{
    use Dispatchable, SerializesModels;

    public function __construct(public Advertisement $advertisement) {}
}
