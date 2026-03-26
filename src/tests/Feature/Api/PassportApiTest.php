<?php

use App\Domain\Passport\Models\Passport;
use App\Support\PassportFilters;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\getJson;

beforeEach(function () {
    Cache::flush();
});

it('returns paginated passports with metadata', function () {
    Passport::factory()->count(30)->create();

    $response = getJson('/api/v1/passports?per_page=10&page=2&sort=dateOfPublish&sort_dir=desc');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'request_number', 'first_name', 'last_name', 'location', 'date_of_publish'],
            ],
            'meta' => ['current_page', 'per_page', 'total', 'last_page', 'has_more', 'page_size', 'page_size_options'],
            'links' => ['first', 'last', 'prev', 'next'],
            'filters',
        ])
        ->assertJsonPath('meta.current_page', 2)
        ->assertJsonPath('meta.per_page', 10)
        ->assertJsonPath('meta.page_size', 10)
        ->assertJsonCount(count(PassportFilters::pageSizeOptions()), 'meta.page_size_options');
});

it('filters passports by request number via the api', function () {
    Passport::factory()->create(['requestNumber' => 'AA12345']);
    Passport::factory()->create(['requestNumber' => 'BB67890']);

    $response = getJson('/api/v1/passports?request_number=AA1');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.request_number', 'AA12345');
});

it('filters passports by composite name via the api', function () {
    $match = Passport::factory()->create([
        'firstName' => 'Lensa',
        'middleName' => 'Amanuel',
        'lastName' => 'Bekele',
    ]);

    Passport::factory()->create([
        'firstName' => 'Lensa',
        'middleName' => 'Dawit',
        'lastName' => 'Haile',
    ]);

    $response = getJson('/api/v1/passports?name=lensa%20amanuel%20bekele');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $match->id)
        ->assertJsonPath('filters.name', 'Lensa Amanuel Bekele');
});

it('validates short request numbers are rejected', function () {
    $response = getJson('/api/v1/passports?request_number=AA');

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['request_number']);
});

it('shows a single passport resource', function () {
    $passport = Passport::factory()->create();

    $response = getJson('/api/v1/passports/'.$passport->id);

    $response->assertOk()
        ->assertJsonPath('data.id', $passport->id)
        ->assertJsonPath('data.request_number', $passport->requestNumber);
});

it('keeps request number search working for records imported from the new four-column format', function () {
    Passport::factory()->create([
        'requestNumber' => 'BRPP525001B2D2P',
        'applicationNumber' => 'BRPP525001B2D2P',
        'firstName' => 'Abato',
        'middleName' => 'Anu',
        'lastName' => 'Ahmed',
        'sourceFormat' => 'application_4col',
        'sourceSurname' => 'Anu Ahmed',
        'sourceGivenname' => 'Abato',
    ]);

    $response = getJson('/api/v1/passports?request_number=BRPP5250');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.request_number', 'BRPP525001B2D2P');
});

it('lists distinct locations', function () {
    Passport::query()->delete();

    Passport::factory()->create(['location' => 'Addis Ababa']);
    Passport::factory()->create(['location' => 'Dire Dawa']);
    Passport::factory()->create(['location' => 'Addis Ababa']);

    $response = getJson('/api/v1/locations');

    $response->assertOk()
        ->assertJsonPath('meta.count', 2)
        ->assertJsonCount(2, 'data');
});
