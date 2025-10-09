<?php

namespace App\Observers;

use App\Domain\Advertisement\Models\Advertisement;
use Illuminate\Support\Facades\Cache;

class AdvertisementObserver
{
    public function created(Advertisement $advertisement): void
    {
        $this->flushCache();
    }

    public function updated(Advertisement $advertisement): void
    {
        // Reset expiry notification flag if ending date changed
        if ($advertisement->isDirty('ad_ending_date') && $advertisement->ad_ending_date) {
            $advertisement->expiry_notification_sent = false;
            $advertisement->saveQuietly(); // Avoid infinite loop
        }

        $this->flushCache();
    }

    public function deleted(Advertisement $advertisement): void
    {
        $this->flushCache();
    }

    public function restored(Advertisement $advertisement): void
    {
        $this->flushCache();
    }

    protected function flushCache(): void
    {
        Cache::tags(['ad_crm'])->flush();
    }
}
