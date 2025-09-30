<?php

use App\Actions\Passport\SearchPassportsAction;
use App\Domain\Passport\Data\PassportSearchParams;
use App\Domain\Passport\Models\Passport;
use Illuminate\Support\Facades\Cache;

it('filters passports by request number prefix', function () {
    $matching = Passport::factory()->create(['requestNumber' => 'AB12345']);
    Passport::factory()->create(['requestNumber' => 'CD67890']);

    $params = PassportSearchParams::fromArray([
        'request_number' => 'AB1',
    ], 'test');

    $results = Passport::query()->filter($params)->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->is($matching))->toBeTrue();
});

it('applies location and publish date filters together', function () {
    $matching = Passport::factory()->create([
        'location' => 'Addis Ababa',
        'dateOfPublish' => '2024-09-01',
    ]);

    Passport::factory()->create([
        'location' => 'Addis Ababa',
        'dateOfPublish' => '2024-08-01',
    ]);

    Passport::factory()->create([
        'location' => 'Dire Dawa',
        'dateOfPublish' => '2024-09-01',
    ]);

    $params = PassportSearchParams::fromArray([
        'location' => 'Addis Ababa',
        'published_after' => '2024-08-15',
    ], 'test');

    $results = Passport::query()->filter($params)->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->is($matching))->toBeTrue();
});

it('uses the shared action to limit results when not paginating', function () {
    foreach (range(0, 2) as $index) {
        Passport::factory()->create(['requestNumber' => 'ZX1234'.$index]);
    }

    $params = PassportSearchParams::fromArray([
        'request_number' => 'ZX1234',
    ], 'web');

    $action = new SearchPassportsAction(Cache::store());
    $results = $action->execute($params, useCache: false);

    expect($results)->toHaveCount(3);
});

it('paginates when per_page is provided', function () {
    Passport::factory()->count(30)->create();

    $params = PassportSearchParams::fromArray([
        'per_page' => 10,
        'page' => 1,
    ], 'api');

    $action = new SearchPassportsAction(Cache::store());
    $paginator = $action->execute($params, useCache: false);

    expect($paginator->perPage())->toBe(10)
        ->and($paginator->currentPage())->toBe(1)
        ->and($paginator->total())->toBeGreaterThanOrEqual(30);
});

it('splits composite name queries into first middle and last filters', function () {
    $match = Passport::factory()->create([
        'firstName' => 'Abebe',
        'middleName' => 'Bekele',
        'lastName' => 'Tesfaye',
    ]);

    Passport::factory()->create([
        'firstName' => 'Abel',
        'middleName' => 'B',
        'lastName' => 'Tesfaye',
    ]);

    $params = PassportSearchParams::fromArray([
        'name' => '  abebe    bekele tesfaye  ',
    ], 'test');

    $results = Passport::query()->filter($params)->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->is($match))->toBeTrue();
});

it('infers request number searches from the generic query parameter', function () {
    $params = PassportSearchParams::fromArray([
        'query' => 'aa-12345',
    ], 'test');

    expect($params->filters()['request_number'])->toBe('AA12345');
});

it('falls back to name search when the generic query has no digits', function () {
    $params = PassportSearchParams::fromArray([
        'query' => 'Lensa',
    ], 'test');

    expect($params->filters()['first_name'])->toBe('Lensa');
});

it('allows page size alias for pagination', function () {
    $params = PassportSearchParams::fromArray([
        'page_size' => 30,
        'page' => 2,
    ], 'api');

    expect($params->perPage())->toBe(30)
        ->and($params->page())->toBe(2);
});
