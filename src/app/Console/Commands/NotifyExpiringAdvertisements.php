<?php

namespace App\Console\Commands;

use App\Domain\Advertisement\Models\Advertisement;
use App\Events\AdvertisementExpiring;
use Illuminate\Console\Command;

class NotifyExpiringAdvertisements extends Command
{
    protected $signature = 'advertisements:notify-expiring {--days=3 : Number of days before expiry to notify}';

    protected $description = 'Send notifications for advertisements expiring soon';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $this->info("Checking for advertisements expiring in {$days} days...");

        $expiring = Advertisement::expiringSoon($days)->get();

        if ($expiring->isEmpty()) {
            $this->info('No advertisements expiring soon.');
            return self::SUCCESS;
        }

        $this->info("Found {$expiring->count()} advertisement(s) expiring soon.");

        foreach ($expiring as $advertisement) {
            $this->line("Processing: {$advertisement->ad_title} (Slot: {$advertisement->ad_slot_number})");
            
            event(new AdvertisementExpiring($advertisement));
            
            $advertisement->markExpiryNotificationSent();
            
            $this->info("  ✓ Notification sent and marked");
        }

        $this->info('Done!');
        return self::SUCCESS;
    }
}
