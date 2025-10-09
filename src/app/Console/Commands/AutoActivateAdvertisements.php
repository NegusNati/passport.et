<?php

namespace App\Console\Commands;

use App\Domain\Advertisement\Models\Advertisement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class AutoActivateAdvertisements extends Command
{
    protected $signature = 'advertisements:auto-activate';

    protected $description = 'Automatically activate scheduled advertisements when their publish date arrives';

    public function handle(): int
    {
        $this->info('Checking for advertisements to activate...');

        $toActivate = Advertisement::where('status', Advertisement::STATUS_SCHEDULED)
            ->where('ad_published_date', '<=', now()->toDateString())
            ->where('payment_status', Advertisement::PAYMENT_PAID)
            ->get();

        if ($toActivate->isEmpty()) {
            $this->info('No advertisements to activate.');
            return self::SUCCESS;
        }

        $this->info("Found {$toActivate->count()} advertisement(s) to activate.");

        foreach ($toActivate as $advertisement) {
            $this->line("Activating: {$advertisement->ad_title} (Slot: {$advertisement->ad_slot_number})");
            
            $advertisement->update(['status' => Advertisement::STATUS_ACTIVE]);
            
            $this->info("  ✓ Activated");
        }

        // Flush cache to reflect new active ads
        Cache::tags(['ad_crm'])->flush();

        $this->info('Done!');
        return self::SUCCESS;
    }
}
