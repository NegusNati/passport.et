<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

it('registers a new user and returns a token', function () {
    $payload = [
        'first_name' => 'Abebe',
        'last_name' => 'Bekele',
        'phone_number' => '0912000000',
        'email' => 'abebe@example.com',
        'password' => 'password123',
    ];

    $response = postJson('/api/v1/auth/register', $payload);

    $response->assertCreated()
        ->assertJsonStructure([
            'token_type',
            'token',
            'user' => ['id', 'email', 'phone_number'],
        ]);

    $user = User::where('phone_number', $payload['phone_number'])->first();

    expect($user)->not->toBeNull();
    expect(Hash::check('password123', $user->password))->toBeTrue();
});

it('authenticates a user and returns a token', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $response = postJson('/api/v1/auth/login', [
        'phone_number' => $user->phone_number,
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
        'phone_number' => $user->phone_number,
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
