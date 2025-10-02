<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;

beforeEach(function () {
    Role::findOrCreate('admin', 'web');
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
            'meta',
        ])
        ->assertJsonPath('meta.per_page', 25);
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
