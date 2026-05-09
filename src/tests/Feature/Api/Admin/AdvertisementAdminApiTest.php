<?php

namespace Tests\Feature\Api\Admin;

use App\Domain\Advertisement\Models\Advertisement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdvertisementAdminApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin role if it doesn't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);
    }

    public function test_admin_can_list_advertisements(): void
    {
        Sanctum::actingAs($this->admin);

        Advertisement::factory()->count(3)->create();

        $response = $this->getJson(route('api.v1.admin.advertisements.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'ad_slot_number',
                        'ad_title',
                        'status',
                        'package_type',
                    ],
                ],
                'meta',
            ]);
    }

    public function test_admin_can_filter_advertisements_by_status(): void
    {
        Sanctum::actingAs($this->admin);

        Advertisement::factory()->create(['status' => Advertisement::STATUS_ACTIVE]);
        Advertisement::factory()->create(['status' => Advertisement::STATUS_DRAFT]);

        $response = $this->getJson(route('api.v1.admin.advertisements.index', [
            'status' => Advertisement::STATUS_ACTIVE,
        ]));

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_admin_can_view_single_advertisement(): void
    {
        Sanctum::actingAs($this->admin);

        $advertisement = Advertisement::factory()->create();

        $response = $this->getJson(route('api.v1.admin.advertisements.show', $advertisement));

        $response->assertOk()
            ->assertJsonPath('data.id', $advertisement->id)
            ->assertJsonPath('data.ad_slot_number', $advertisement->ad_slot_number);
    }

    public function test_admin_can_create_advertisement(): void
    {
        Sanctum::actingAs($this->admin);
        Storage::fake('public');

        $response = $this->postJson(route('api.v1.admin.advertisements.store'), [
            'ad_slot_number' => 'homepage-banner-1',
            'slot_code' => 'home-alerts-banner',
            'ad_title' => 'Test Advertisement',
            'alt_text' => 'Test advertisement image',
            'ad_desc' => 'Test description',
            'ad_excerpt' => 'Test excerpt',
            'client_name' => 'Passport',
            'ad_client_link' => 'https://example.com',
            'target_url' => 'https://example.com',
            'package_type' => Advertisement::PACKAGE_MONTHLY,
            'ad_published_date' => now()->addDay()->toDateString(),
            'status' => Advertisement::STATUS_DRAFT,
            'payment_status' => Advertisement::PAYMENT_PENDING,
            'payment_amount' => 500.00,
            'priority' => 10,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.ad_slot_number', 'homepage-banner-1')
            ->assertJsonPath('data.ad_title', 'Test Advertisement');

        $this->assertDatabaseHas('advertisements', [
            'ad_slot_number' => 'homepage-banner-1',
            'slot_code' => 'home-alerts-banner',
            'ad_title' => 'Test Advertisement',
        ]);
    }

    public function test_admin_can_create_active_advertisement_with_past_publish_date(): void
    {
        Sanctum::actingAs($this->admin);
        Storage::fake('public');

        $response = $this->post(route('api.v1.admin.advertisements.store'), [
            'slot_code' => 'home-alerts-banner',
            'ad_title' => 'Already Live Advertisement',
            'alt_text' => 'Already live advertisement image',
            'ad_excerpt' => 'Test excerpt',
            'client_name' => 'Passport',
            'target_url' => 'https://example.com',
            'ad_client_link' => 'https://example.com',
            'package_type' => Advertisement::PACKAGE_MONTHLY,
            'ad_published_date' => now()->subDay()->toDateString(),
            'ad_ending_date' => now()->addDays(10)->toDateString(),
            'status' => Advertisement::STATUS_ACTIVE,
            'payment_status' => Advertisement::PAYMENT_PENDING,
            'payment_amount' => 500.00,
            'ad_desktop_asset' => UploadedFile::fake()->image('desktop.png', 1200, 300),
            'ad_desktop_dark_asset' => UploadedFile::fake()->image('desktop-dark.png', 1200, 300),
            'ad_mobile_asset' => UploadedFile::fake()->image('mobile.png', 640, 360),
            'ad_mobile_dark_asset' => UploadedFile::fake()->image('mobile-dark.png', 640, 360),
        ], ['Accept' => 'application/json']);

        $response->assertCreated()
            ->assertJsonPath('data.status', Advertisement::STATUS_ACTIVE)
            ->assertJsonPath('data.ad_published_date', now()->subDay()->toDateString());

        $this->assertStringContainsString('/storage/advertisements/desktop-dark/', $response->json('data.ad_desktop_dark_asset'));
        $this->assertStringContainsString('/storage/advertisements/mobile-dark/', $response->json('data.ad_mobile_dark_asset'));

        $this->assertDatabaseHas('advertisements', [
            'ad_title' => 'Already Live Advertisement',
            'status' => Advertisement::STATUS_ACTIVE,
            'payment_status' => Advertisement::PAYMENT_PENDING,
            'ad_published_date' => now()->subDay()->toDateString(),
        ]);
    }

    public function test_admin_can_list_advertisement_slots(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson(route('api.v1.admin.advertisement-slots.index'));

        $response->assertOk()
            ->assertJsonPath('data.0.is_active', true)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'code',
                        'name',
                        'page_context',
                        'format',
                        'desktop_width',
                        'desktop_height',
                        'mobile_width',
                        'mobile_height',
                        'is_active',
                    ],
                ],
            ]);
    }

    public function test_publishable_ad_requires_desktop_mobile_assets_target_and_alt_text(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson(route('api.v1.admin.advertisements.store'), [
            'slot_code' => 'home-alerts-banner',
            'ad_title' => 'Test Advertisement',
            'ad_excerpt' => 'Test excerpt',
            'client_name' => 'Passport',
            'package_type' => Advertisement::PACKAGE_MONTHLY,
            'ad_published_date' => now()->addDay()->toDateString(),
            'status' => Advertisement::STATUS_ACTIVE,
            'payment_status' => Advertisement::PAYMENT_PAID,
            'payment_amount' => 500.00,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'target_url',
                'ad_desktop_asset',
                'ad_mobile_asset',
            ]);
    }

    public function test_publishable_ads_cannot_overlap_same_slot_schedule(): void
    {
        Sanctum::actingAs($this->admin);
        Storage::fake('public');

        Advertisement::factory()->create([
            'slot_code' => 'home-alerts-banner',
            'status' => Advertisement::STATUS_ACTIVE,
            'ad_published_date' => now()->addDay()->toDateString(),
            'ad_ending_date' => now()->addDays(10)->toDateString(),
            'payment_status' => Advertisement::PAYMENT_PAID,
        ]);

        $response = $this->post(route('api.v1.admin.advertisements.store'), [
            'slot_code' => 'home-alerts-banner',
            'ad_title' => 'Overlapping Advertisement',
            'alt_text' => 'Overlapping ad image',
            'ad_excerpt' => 'Test excerpt',
            'client_name' => 'Passport',
            'target_url' => 'https://example.com',
            'ad_client_link' => 'https://example.com',
            'package_type' => Advertisement::PACKAGE_MONTHLY,
            'ad_published_date' => now()->addDays(2)->toDateString(),
            'ad_ending_date' => now()->addDays(5)->toDateString(),
            'status' => Advertisement::STATUS_ACTIVE,
            'payment_status' => Advertisement::PAYMENT_PAID,
            'payment_amount' => 500.00,
            'ad_desktop_asset' => UploadedFile::fake()->image('desktop.png', 1200, 300),
            'ad_mobile_asset' => UploadedFile::fake()->image('mobile.png', 640, 360),
        ], ['Accept' => 'application/json']);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['slot_code']);
    }

    public function test_admin_can_update_advertisement(): void
    {
        Sanctum::actingAs($this->admin);

        $advertisement = Advertisement::factory()->create([
            'ad_title' => 'Original Title',
        ]);

        $response = $this->patchJson(
            route('api.v1.admin.advertisements.update', $advertisement),
            ['ad_title' => 'Updated Title']
        );

        $response->assertOk()
            ->assertJsonPath('data.ad_title', 'Updated Title');

        $this->assertDatabaseHas('advertisements', [
            'id' => $advertisement->id,
            'ad_title' => 'Updated Title',
        ]);
    }

    public function test_admin_can_update_and_remove_dark_mode_assets(): void
    {
        Sanctum::actingAs($this->admin);
        Storage::fake('public');

        Storage::disk('public')->put('advertisements/desktop-dark/existing.png', 'desktop-dark');
        Storage::disk('public')->put('advertisements/mobile-dark/existing.png', 'mobile-dark');

        $advertisement = Advertisement::factory()->create([
            'ad_desktop_dark_asset' => 'advertisements/desktop-dark/existing.png',
            'ad_mobile_dark_asset' => 'advertisements/mobile-dark/existing.png',
        ]);

        $response = $this->post(
            route('api.v1.admin.advertisements.update', $advertisement),
            [
                '_method' => 'PATCH',
                'ad_desktop_dark_asset' => UploadedFile::fake()->image('desktop-dark-new.png', 1200, 300),
                'remove_ad_mobile_dark_asset' => '1',
            ],
            ['Accept' => 'application/json']
        );

        $response->assertOk()
            ->assertJsonPath('data.ad_mobile_dark_asset', null);

        $this->assertStringContainsString('/storage/advertisements/desktop-dark/', $response->json('data.ad_desktop_dark_asset'));

        Storage::disk('public')->assertMissing('advertisements/desktop-dark/existing.png');
        Storage::disk('public')->assertMissing('advertisements/mobile-dark/existing.png');

        $advertisement->refresh();

        $this->assertNotNull($advertisement->ad_desktop_dark_asset);
        $this->assertNull($advertisement->ad_mobile_dark_asset);
    }

    public function test_admin_can_activate_pending_payment_advertisement_with_current_publish_date(): void
    {
        Sanctum::actingAs($this->admin);

        $advertisement = Advertisement::factory()->create([
            'slot_code' => 'articles-list-top',
            'status' => Advertisement::STATUS_SCHEDULED,
            'payment_status' => Advertisement::PAYMENT_PENDING,
            'ad_published_date' => now()->toDateString(),
            'ad_desktop_asset' => 'advertisements/desktop/existing.png',
            'ad_mobile_asset' => 'advertisements/mobile/existing.png',
        ]);

        $response = $this->patchJson(
            route('api.v1.admin.advertisements.update', $advertisement),
            ['status' => Advertisement::STATUS_ACTIVE]
        );

        $response->assertOk()
            ->assertJsonPath('data.status', Advertisement::STATUS_ACTIVE)
            ->assertJsonPath('data.payment_status', Advertisement::PAYMENT_PENDING);

        $this->assertDatabaseHas('advertisements', [
            'id' => $advertisement->id,
            'status' => Advertisement::STATUS_ACTIVE,
            'payment_status' => Advertisement::PAYMENT_PENDING,
        ]);
    }

    public function test_admin_can_delete_advertisement(): void
    {
        Sanctum::actingAs($this->admin);

        $advertisement = Advertisement::factory()->create();

        $response = $this->deleteJson(route('api.v1.admin.advertisements.destroy', $advertisement));

        $response->assertNoContent();

        $this->assertSoftDeleted('advertisements', [
            'id' => $advertisement->id,
        ]);
    }

    public function test_admin_can_restore_deleted_advertisement(): void
    {
        Sanctum::actingAs($this->admin);

        $advertisement = Advertisement::factory()->create();
        $advertisement->delete();

        $response = $this->postJson(route('api.v1.admin.advertisements.restore', $advertisement->id));

        $response->assertOk();

        $this->assertDatabaseHas('advertisements', [
            'id' => $advertisement->id,
            'deleted_at' => null,
        ]);
    }

    public function test_admin_can_view_stats(): void
    {
        Sanctum::actingAs($this->admin);

        Advertisement::factory()->create([
            'status' => Advertisement::STATUS_ACTIVE,
            'ad_published_date' => now()->subDays(10)->toDateString(),
            'ad_ending_date' => now()->addDays(20)->toDateString(),
            'payment_status' => Advertisement::PAYMENT_PAID,
            'payment_amount' => 1500.50,
            'impressions_count' => 1000,
            'clicks_count' => 50,
        ]);
        Advertisement::factory()->create([
            'status' => Advertisement::STATUS_ACTIVE,
            'ad_published_date' => now()->subDays(3)->toDateString(),
            'ad_ending_date' => now()->addDays(2)->toDateString(),
            'payment_status' => Advertisement::PAYMENT_PENDING,
            'payment_amount' => 500,
            'impressions_count' => 500,
            'clicks_count' => 25,
        ]);

        $response = $this->getJson(route('api.v1.admin.advertisements.stats'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'total_advertisements',
                    'total_active',
                    'total_draft',
                    'total_scheduled',
                    'total_paused',
                    'expiring_soon',
                    'expired_pending_renewal',
                    'paid_advertisements',
                    'pending_payment',
                    'total_impressions',
                    'total_clicks',
                    'avg_ctr',
                    'total_revenue',
                    'revenue_this_month',
                    'revenue_last_30_days',
                    'top_performers' => [
                        '*' => [
                            'id',
                            'ad_title',
                            'slot_code',
                            'status',
                            'impressions_count',
                            'clicks_count',
                            'ctr',
                            'payment_amount',
                        ],
                    ],
                ],
            ])
            ->assertJsonPath('data.total_advertisements', 2)
            ->assertJsonPath('data.total_active', 2)
            ->assertJsonPath('data.expiring_soon', 1)
            ->assertJsonPath('data.total_impressions', 1500)
            ->assertJsonPath('data.total_clicks', 75)
            ->assertJsonPath('data.avg_ctr', 5)
            ->assertJsonPath('data.total_revenue', 1500.50)
            ->assertJsonPath('data.pending_payment', 1);

        $this->assertIsInt($response->json('data.total_impressions'));
        $this->assertIsNumeric($response->json('data.revenue_this_month'));
    }

    public function test_non_admin_cannot_access_advertisements(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.admin.advertisements.index'));

        $response->assertForbidden();
    }

    public function test_guest_cannot_access_advertisements(): void
    {
        $response = $this->getJson(route('api.v1.admin.advertisements.index'));

        $response->assertUnauthorized();
    }

    public function test_cannot_create_advertisement_with_duplicate_slot(): void
    {
        Sanctum::actingAs($this->admin);

        Advertisement::factory()->create(['ad_slot_number' => 'homepage-banner-1']);

        $response = $this->postJson(route('api.v1.admin.advertisements.store'), [
            'ad_slot_number' => 'homepage-banner-1',
            'slot_code' => 'home-alerts-banner',
            'ad_title' => 'Test Advertisement',
            'package_type' => Advertisement::PACKAGE_MONTHLY,
            'ad_published_date' => now()->toDateString(),
            'status' => Advertisement::STATUS_DRAFT,
            'payment_status' => Advertisement::PAYMENT_PENDING,
            'payment_amount' => 500.00,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['ad_slot_number']);
    }

    public function test_ending_date_must_be_after_published_date(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson(route('api.v1.admin.advertisements.store'), [
            'ad_slot_number' => 'homepage-banner-1',
            'slot_code' => 'home-alerts-banner',
            'ad_title' => 'Test Advertisement',
            'package_type' => Advertisement::PACKAGE_MONTHLY,
            'ad_published_date' => now()->addDays(5)->toDateString(),
            'ad_ending_date' => now()->toDateString(),
            'status' => Advertisement::STATUS_DRAFT,
            'payment_status' => Advertisement::PAYMENT_PENDING,
            'payment_amount' => 500.00,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['ad_ending_date']);
    }
}
