<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

it('authenticates a user and returns a token', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $response = postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'testsuite',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'token_type',
            'token',
            'user' => ['id', 'email'],
        ]);
});

it('rejects invalid credentials', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $response = postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('code', 'invalid_credentials');
});

it('returns the authenticated user profile', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $token = $user->createToken('testsuite')->plainTextToken;

    $response = getJson('/api/v1/auth/me', [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', $user->email);
});

it('revokes the current access token on logout', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $token = $user->createToken('testsuite')->plainTextToken;

    $response = postJson('/api/v1/auth/logout', [], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertNoContent();
    expect($user->fresh()->tokens()->count())->toBe(0);
});
