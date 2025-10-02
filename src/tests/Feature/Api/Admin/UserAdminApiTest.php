<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;

beforeEach(function () {
    Role::findOrCreate('admin', 'web');
    Role::findOrCreate('editor', 'web');
});

it('lists users for admins', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    User::factory()->count(3)->create();

    $token = $admin->createToken('testsuite')->plainTextToken;

    $response = getJson('/api/v1/admin/users', [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'email', 'first_name', 'last_name', 'is_admin', 'roles'],
            ],
            'links',
            'meta' => ['page_size_options', 'has_more', 'page_size', 'total'],
            'filters',
            'sort' => ['column', 'direction'],
        ])
        ->assertJsonPath('meta.page_size', 25)
        ->assertJsonPath('sort.column', 'created_at');
});

it('supports searching and role filters', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $target = User::factory()->create([
        'first_name' => 'Searchable',
        'last_name' => 'Person',
        'email' => 'search@example.com',
        'phone_number' => '0911000000',
    ]);
    $target->assignRole('editor');

    User::factory()->create([
        'first_name' => 'Other',
        'email' => 'other@example.com',
    ]);

    $token = $admin->createToken('testsuite')->plainTextToken;

    $response = getJson('/api/v1/admin/users?search=Searchable&role=editor', [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $target->id)
        ->assertJsonPath('filters.role', 'editor');
});

it('filters by admin flag and verification', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $verifiedAdmin = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    $verifiedAdmin->assignRole('admin');

    $token = $admin->createToken('testsuite')->plainTextToken;

    $response = getJson('/api/v1/admin/users?is_admin=1&email_verified=1', [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertOk()
        ->assertJsonMissingPath('filters.search')
        ->assertJsonPath('filters.is_admin', true)
        ->assertJsonPath('filters.email_verified', true)
        ->assertJsonCount(2, 'data');
});

it('prevents non-admins from listing users', function () {
    $user = User::factory()->create();
    $token = $user->createToken('testsuite')->plainTextToken;

    $response = getJson('/api/v1/admin/users', [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertForbidden();
});

it('allows admins to grant admin role to another user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $target = User::factory()->create();

    $token = $admin->createToken('testsuite')->plainTextToken;

    $response = patchJson('/api/v1/admin/users/'.$target->id.'/role', [
        'role' => 'admin',
    ], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.id', $target->id)
        ->assertJsonPath('data.is_admin', true);

    expect($target->fresh()->hasRole('admin'))->toBeTrue();
});

it('prevents non-admins from changing roles', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $token = $user->createToken('testsuite')->plainTextToken;

    $response = patchJson('/api/v1/admin/users/'.$target->id.'/role', [
        'role' => 'admin',
    ], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertForbidden();
    expect($target->fresh()->hasRole('admin'))->toBeFalse();
});
