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
                    ]
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
            'ad_title' => 'Test Advertisement',
            'ad_desc' => 'Test description',
            'ad_excerpt' => 'Test excerpt',
            'ad_client_link' => 'https://example.com',
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
            'ad_title' => 'Test Advertisement',
        ]);
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

        Advertisement::factory()->create(['status' => Advertisement::STATUS_ACTIVE]);
        Advertisement::factory()->create([
            'status' => Advertisement::STATUS_ACTIVE,
            'ad_ending_date' => now()->addDays(2),
        ]);

        $response = $this->getJson(route('api.v1.admin.advertisements.stats'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'total_active',
                    'expiring_soon',
                    'expired_pending_renewal',
                    'total_impressions',
                    'total_clicks',
                    'avg_ctr',
                    'revenue_this_month',
                ]
            ]);
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
