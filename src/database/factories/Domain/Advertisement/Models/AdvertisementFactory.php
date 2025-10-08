<?php

namespace Database\Factories\Domain\Advertisement\Models;

use App\Domain\Advertisement\Models\Advertisement;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdvertisementFactory extends Factory
{
    protected $model = Advertisement::class;

    public function definition(): array
    {
        return [
            'ad_slot_number' => 'slot-' . $this->faker->unique()->numberBetween(1, 10000),
            'ad_title' => $this->faker->sentence(4),
            'ad_desc' => $this->faker->paragraph(3),
            'ad_excerpt' => $this->faker->sentence(10),
            'ad_desktop_asset' => null,
            'ad_mobile_asset' => null,
            'ad_client_link' => $this->faker->url(),
            'status' => Advertisement::STATUS_DRAFT, // Default to draft to avoid test pollution
            'package_type' => $this->faker->randomElement(Advertisement::packageTypes()),
            'ad_published_date' => now()->addDays(rand(1, 30))->toDateString(), // Future date by default
            'ad_ending_date' => now()->addDays(rand(31, 90))->toDateString(),
            'payment_status' => Advertisement::PAYMENT_PENDING, // Default to pending
            'payment_amount' => $this->faker->randomFloat(2, 100, 5000),
            'client_name' => $this->faker->company(),
            'advertisement_request_id' => null,
            'impressions_count' => 0,
            'clicks_count' => 0,
            'priority' => 0,
            'expiry_notification_sent' => false,
            'admin_notes' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Advertisement::STATUS_ACTIVE,
            'ad_published_date' => now()->subDay()->toDateString(),
            'ad_ending_date' => now()->addDays(30)->toDateString(),
            'payment_status' => Advertisement::PAYMENT_PAID,
            'impressions_count' => rand(100, 10000),
            'clicks_count' => rand(10, 500),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Advertisement::STATUS_DRAFT,
            'payment_status' => Advertisement::PAYMENT_PENDING,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Advertisement::STATUS_SCHEDULED,
            'ad_published_date' => now()->addDay(),
            'payment_status' => Advertisement::PAYMENT_PAID,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Advertisement::STATUS_EXPIRED,
            'ad_published_date' => now()->subDays(60),
            'ad_ending_date' => now()->subDay(),
        ]);
    }

    public function expiringSoon(int $days = 3): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Advertisement::STATUS_ACTIVE,
            'ad_published_date' => now()->subDay(),
            'ad_ending_date' => now()->addDays($days),
            'expiry_notification_sent' => false,
        ]);
    }
}
