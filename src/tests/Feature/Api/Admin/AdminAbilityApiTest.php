<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\getJson;

beforeEach(function () {
    Role::findOrCreate('admin', 'web');
});

it('returns ability flags for admin users', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $token = $admin->createToken('testsuite')->plainTextToken;

    $response = getJson('/api/v1/admin/abilities', [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.abilities.viewPulse', true)
        ->assertJsonPath('data.abilities.viewHorizon', true)
        ->assertJsonPath('data.abilities.manageArticles', true);
});

it('blocks non-admin users from fetching abilities', function () {
    $user = User::factory()->create();

    $token = $user->createToken('testsuite')->plainTextToken;

    $response = getJson('/api/v1/admin/abilities', [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertForbidden();
});
