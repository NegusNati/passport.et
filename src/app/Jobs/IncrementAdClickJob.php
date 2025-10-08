<?php

namespace App\Jobs;

use App\Domain\Advertisement\Models\Advertisement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IncrementAdClickJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $advertisementId) {}

    public function handle(): void
    {
        $advertisement = Advertisement::find($this->advertisementId);

        if ($advertisement) {
            $advertisement->incrementClicks();
        }
    }

    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(5);
    }
}
