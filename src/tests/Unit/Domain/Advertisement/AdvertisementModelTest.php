<?php

namespace Tests\Unit\Domain\Advertisement;

use App\Domain\Advertisement\Models\Advertisement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvertisementModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_scope_filters_correctly(): void
    {
        // Active ad (published in the past, ends in the future)
        $activeAd = Advertisement::factory()->create([
            'status' => Advertisement::STATUS_ACTIVE,
            'ad_published_date' => now()->subDay(),
            'ad_ending_date' => now()->addDays(10),
        ]);

        // Draft ad
        Advertisement::factory()->create([
            'status' => Advertisement::STATUS_DRAFT,
            'ad_published_date' => now()->subDay(),
        ]);

        // Expired ad
        Advertisement::factory()->create([
            'status' => Advertisement::STATUS_ACTIVE,
            'ad_published_date' => now()->subDays(20),
            'ad_ending_date' => now()->subDay(),
        ]);

        // Future scheduled ad
        Advertisement::factory()->create([
            'status' => Advertisement::STATUS_ACTIVE,
            'ad_published_date' => now()->addDay(),
        ]);

        $activeAds = Advertisement::active()->get();

        $this->assertCount(1, $activeAds);
        $this->assertTrue($activeAds->contains($activeAd));
    }

    public function test_expiring_soon_scope_filters_correctly(): void
    {
        // Expiring in 2 days
        $expiringSoon = Advertisement::factory()->create([
            'status' => Advertisement::STATUS_ACTIVE,
            'ad_published_date' => now()->subDay(),
            'ad_ending_date' => now()->addDays(2),
            'expiry_notification_sent' => false,
        ]);

        // Expiring in 10 days
        Advertisement::factory()->create([
            'status' => Advertisement::STATUS_ACTIVE,
            'ad_published_date' => now()->subDay(),
            'ad_ending_date' => now()->addDays(10),
            'expiry_notification_sent' => false,
        ]);

        // Already notified
        Advertisement::factory()->create([
            'status' => Advertisement::STATUS_ACTIVE,
            'ad_published_date' => now()->subDay(),
            'ad_ending_date' => now()->addDays(2),
            'expiry_notification_sent' => true,
        ]);

        $expiring = Advertisement::expiringSoon(3)->get();

        $this->assertCount(1, $expiring);
        $this->assertTrue($expiring->contains($expiringSoon));
    }

    public function test_expired_scope_filters_correctly(): void
    {
        // Expired (active status but ending date passed)
        $expired = Advertisement::factory()->create([
            'status' => Advertisement::STATUS_ACTIVE,
            'ad_published_date' => now()->subDays(30),
            'ad_ending_date' => now()->subDay(),
        ]);

        // Active (not expired)
        Advertisement::factory()->create([
            'status' => Advertisement::STATUS_ACTIVE,
            'ad_published_date' => now()->subDay(),
            'ad_ending_date' => now()->addDays(10),
        ]);

        // Already marked as expired
        Advertisement::factory()->create([
            'status' => Advertisement::STATUS_EXPIRED,
            'ad_published_date' => now()->subDays(30),
            'ad_ending_date' => now()->subDay(),
        ]);

        $expiredAds = Advertisement::expired()->get();

        $this->assertCount(1, $expiredAds);
        $this->assertTrue($expiredAds->contains($expired));
    }

    public function test_is_active_returns_correct_value(): void
    {
        $activeAd = Advertisement::factory()->create([
            'status' => Advertisement::STATUS_ACTIVE,
            'ad_published_date' => now()->subDay(),
            'ad_ending_date' => now()->addDays(10),
        ]);

        $inactiveAd = Advertisement::factory()->create([
            'status' => Advertisement::STATUS_DRAFT,
        ]);

        $expiredAd = Advertisement::factory()->create([
            'status' => Advertisement::STATUS_ACTIVE,
            'ad_published_date' => now()->subDays(30),
            'ad_ending_date' => now()->subDay(),
        ]);

        $this->assertTrue($activeAd->isActive());
        $this->assertFalse($inactiveAd->isActive());
        $this->assertFalse($expiredAd->isActive());
    }

    public function test_is_expired_returns_correct_value(): void
    {
        $expiredAd = Advertisement::factory()->create([
            'ad_ending_date' => now()->subDay(),
        ]);

        $activeAd = Advertisement::factory()->create([
            'ad_ending_date' => now()->addDays(10),
        ]);

        $noEndDate = Advertisement::factory()->create([
            'ad_ending_date' => null,
        ]);

        $this->assertTrue($expiredAd->isExpired());
        $this->assertFalse($activeAd->isExpired());
        $this->assertFalse($noEndDate->isExpired());
    }

    public function test_days_until_expiry_calculates_correctly(): void
    {
        $ad = Advertisement::factory()->create([
            'ad_ending_date' => now()->addDays(5),
        ]);

        $this->assertEquals(5, $ad->daysUntilExpiry());
    }

    public function test_days_until_expiry_returns_null_when_no_end_date(): void
    {
        $ad = Advertisement::factory()->create([
            'ad_ending_date' => null,
        ]);

        $this->assertNull($ad->daysUntilExpiry());
    }

    public function test_increment_impressions_increments_count(): void
    {
        $ad = Advertisement::factory()->create(['impressions_count' => 100]);

        $ad->incrementImpressions();
        $ad->refresh();

        $this->assertEquals(101, $ad->impressions_count);
    }

    public function test_increment_clicks_increments_count(): void
    {
        $ad = Advertisement::factory()->create(['clicks_count' => 50]);

        $ad->incrementClicks();
        $ad->refresh();

        $this->assertEquals(51, $ad->clicks_count);
    }

    public function test_mark_expiry_notification_sent_updates_flag(): void
    {
        $ad = Advertisement::factory()->create(['expiry_notification_sent' => false]);

        $ad->markExpiryNotificationSent();
        $ad->refresh();

        $this->assertTrue($ad->expiry_notification_sent);
    }

    public function test_by_slot_scope_filters_correctly(): void
    {
        $targetAd = Advertisement::factory()->create(['ad_slot_number' => 'homepage-banner-1']);
        Advertisement::factory()->create(['ad_slot_number' => 'sidebar-ad-1']);

        $result = Advertisement::bySlot('homepage-banner-1')->get();

        $this->assertCount(1, $result);
        $this->assertTrue($result->contains($targetAd));
    }
}
