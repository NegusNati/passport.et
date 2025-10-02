<?php

use App\Domain\Article\Models\Article;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\postJson;
use function Pest\Laravel\patchJson;

beforeEach(function () {
    Role::findOrCreate('admin', 'web');
});

it('can create an article for a specified author', function () {
    $admin = User::factory()->create([
        'password' => Hash::make('password'),
    ]);
    $admin->assignRole('admin');

    $author = User::factory()->create();

    $token = $admin->createToken('testsuite')->plainTextToken;

    $payload = [
        'title' => 'API Created Article',
        'status' => 'draft',
        'author_id' => $author->id,
    ];

    $response = postJson('/api/v1/admin/articles', $payload, [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.author.id', $author->id);

    $article = Article::where('title', 'API Created Article')->first();

    expect($article)->not->toBeNull();
    expect($article->author_id)->toBe($author->id);
});

it('can reassign an article to a different author', function () {
    $admin = User::factory()->create([
        'password' => Hash::make('password'),
    ]);
    $admin->assignRole('admin');

    $originalAuthor = User::factory()->create();
    $newAuthor = User::factory()->create();

    $article = Article::create([
        'author_id' => $originalAuthor->id,
        'title' => 'Reassignable Article',
        'slug' => 'reassignable-article',
        'status' => 'draft',
        'reading_time' => 1,
        'word_count' => 10,
    ]);

    $token = $admin->createToken('testsuite')->plainTextToken;

    $response = patchJson('/api/v1/admin/articles/'.$article->slug, [
        'author_id' => $newAuthor->id,
    ], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.author.id', $newAuthor->id);

    expect($article->fresh()->author_id)->toBe($newAuthor->id);
});
