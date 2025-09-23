<?php

use App\Domain\Passport\Models\Passport;

use function Pest\Laravel\getJson;

it('returns paginated passports with metadata', function () {
    Passport::factory()->count(30)->create();

    $response = getJson('/api/v1/passports?per_page=10&page=2&sort=dateOfPublish&sort_dir=desc');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'request_number', 'first_name', 'last_name', 'location', 'date_of_publish'],
            ],
            'meta' => ['current_page', 'per_page', 'total', 'last_page', 'has_more'],
            'links' => ['first', 'last', 'prev', 'next'],
            'filters',
        ])
        ->assertJsonPath('meta.current_page', 2)
        ->assertJsonPath('meta.per_page', 10);
});

it('filters passports by request number via the api', function () {
    Passport::factory()->create(['requestNumber' => 'AA12345']);
    Passport::factory()->create(['requestNumber' => 'BB67890']);

    $response = getJson('/api/v1/passports?request_number=AA1');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.request_number', 'AA12345');
});

it('shows a single passport resource', function () {
    $passport = Passport::factory()->create();

    $response = getJson('/api/v1/passports/'.$passport->id);

    $response->assertOk()
        ->assertJsonPath('data.id', $passport->id)
        ->assertJsonPath('data.request_number', $passport->requestNumber);
});

it('lists distinct locations', function () {
    Passport::factory()->create(['location' => 'Addis Ababa']);
    Passport::factory()->create(['location' => 'Dire Dawa']);
    Passport::factory()->create(['location' => 'Addis Ababa']);

    $response = getJson('/api/v1/locations');

    $response->assertOk()
        ->assertJsonPath('meta.count', 2)
        ->assertJsonCount(2, 'data');
});
