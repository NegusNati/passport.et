<?php

namespace App\Console\Commands;

use App\Domain\Advertisement\Models\Advertisement;
use App\Events\AdvertisementExpired;
use Illuminate\Console\Command;

class AutoExpireAdvertisements extends Command
{
    protected $signature = 'advertisements:auto-expire';

    protected $description = 'Automatically expire advertisements that have passed their ending date';

    public function handle(): int
    {
        $this->info('Checking for expired advertisements...');

        $expired = Advertisement::expired()->get();

        if ($expired->isEmpty()) {
            $this->info('No advertisements to expire.');
            return self::SUCCESS;
        }

        $this->info("Found {$expired->count()} expired advertisement(s).");

        foreach ($expired as $advertisement) {
            $this->line("Expiring: {$advertisement->ad_title} (Slot: {$advertisement->ad_slot_number})");
            
            $advertisement->update(['status' => Advertisement::STATUS_EXPIRED]);
            
            event(new AdvertisementExpired($advertisement));
            
            $this->info("  ✓ Marked as expired");
        }

        $this->info('Done!');
        return self::SUCCESS;
    }
}
