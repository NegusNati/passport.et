<?php

use App\Domain\Passport\Enums\PassportPdfSourceFormat;
use App\Domain\Passport\Services\PassportPdfParser;

it('parses legacy five-column rows using column spacing', function () {
    $parser = new PassportPdfParser();

    $text = <<<'TEXT'
Ignored heading
START HERE
No.    NAME    F. NAME    G.F. NAME    REQUEST_No.
1      ABAS    JALETA     ERESO         AIL9610825
2      ABAYNESH      ABEBE      SHARCHO      AIL9555078
TEXT;

    $parsed = $parser->parse($text, 'START HERE');

    expect($parsed->detectedFormat)->toBe(PassportPdfSourceFormat::LegacyFiveColumn)
        ->and($parsed->rows)->toHaveCount(2)
        ->and($parsed->failedRows)->toBe(0)
        ->and($parsed->rows[0]->requestNumber)->toBe('AIL9610825')
        ->and($parsed->rows[0]->firstName)->toBe('ABAS')
        ->and($parsed->rows[0]->middleName)->toBe('JALETA')
        ->and($parsed->rows[0]->lastName)->toBe('ERESO');
});

it('parses application four-column rows and maps them into compatibility fields', function () {
    $parser = new PassportPdfParser();

    $text = <<<'TEXT'
No.    Application Number    Applicant's Surname    Applicant's Givenname
1      BRPP525001B2D2P       Anu Ahmed              Abato
2      BRPP5250028E09P       Zera Rameto            Abbabo
TEXT;

    $parsed = $parser->parse($text);

    expect($parsed->detectedFormat)->toBe(PassportPdfSourceFormat::ApplicationFourColumn)
        ->and($parsed->rows)->toHaveCount(2)
        ->and($parsed->rows[0]->requestNumber)->toBe('BRPP525001B2D2P')
        ->and($parsed->rows[0]->applicationNumber)->toBe('BRPP525001B2D2P')
        ->and($parsed->rows[0]->firstName)->toBe('Abato')
        ->and($parsed->rows[0]->middleName)->toBe('Anu')
        ->and($parsed->rows[0]->lastName)->toBe('Ahmed')
        ->and($parsed->rows[0]->sourceSurname)->toBe('Anu Ahmed')
        ->and($parsed->rows[0]->sourceGivenname)->toBe('Abato');
});

it('supports manual format override and counts malformed rows', function () {
    $parser = new PassportPdfParser();

    $text = <<<'TEXT'
1      BRPP525001B2D2P       Anu Ahmed              Abato
3      BROKEN
2      BRPP5250028E09P       Zera Rameto            Abbabo
TEXT;

    $parsed = $parser->parse($text, null, PassportPdfSourceFormat::ApplicationFourColumn);

    expect($parsed->rows)->toHaveCount(2)
        ->and($parsed->failedRows)->toBe(1);
});

it('preserves wrapped application rows across multiple lines', function () {
    $parser = new PassportPdfParser();

    $text = <<<'TEXT'
No.    Application Number    Applicant's Surname    Applicant's Givenname
1      BRPP525001B2D2P       Very Long
       Surname              Abato
TEXT;

    $parsed = $parser->parse($text);

    expect($parsed->rows)->toHaveCount(1)
        ->and($parsed->failedRows)->toBe(0)
        ->and($parsed->rows[0]->requestNumber)->toBe('BRPP525001B2D2P')
        ->and($parsed->rows[0]->sourceSurname)->toBe('Very Long Surname')
        ->and($parsed->rows[0]->sourceGivenname)->toBe('Abato');
});

it('ignores footer text after the last parsed row', function () {
    $parser = new PassportPdfParser();

    $text = <<<'TEXT'
No.    Application Number    Applicant's Surname    Applicant's Givenname
1      BRPP525001B2D2P       Anu Ahmed              Abato
Page 1 of 1
TEXT;

    $parsed = $parser->parse($text);

    expect($parsed->rows)->toHaveCount(1)
        ->and($parsed->failedRows)->toBe(0)
        ->and($parsed->skippedRows)->toBe(1)
        ->and($parsed->rows[0]->sourceGivenname)->toBe('Abato')
        ->and($parsed->rows[0]->firstName)->toBe('Abato');
});

it('parses single-spaced legacy rows when layout spacing is unavailable', function () {
    $parser = new PassportPdfParser();

    $text = <<<'TEXT'
No. NAME F. NAME G.F. NAME REQUEST_No.
1 ABAS JALETA ERESO AIL9610825
TEXT;

    $parsed = $parser->parse($text, null, PassportPdfSourceFormat::LegacyFiveColumn);

    expect($parsed->rows)->toHaveCount(1)
        ->and($parsed->failedRows)->toBe(0)
        ->and($parsed->rows[0]->requestNumber)->toBe('AIL9610825')
        ->and($parsed->rows[0]->firstName)->toBe('ABAS')
        ->and($parsed->rows[0]->middleName)->toBe('JALETA')
        ->and($parsed->rows[0]->lastName)->toBe('ERESO');
});

it('parses single-spaced application rows when layout spacing is unavailable', function () {
    $parser = new PassportPdfParser();

    $text = <<<'TEXT'
No. Application Number Applicant's Surname Applicant's Givenname
1 BRPP525001B2D2P Anu Ahmed Abato
TEXT;

    $parsed = $parser->parse($text, null, PassportPdfSourceFormat::ApplicationFourColumn);

    expect($parsed->rows)->toHaveCount(1)
        ->and($parsed->failedRows)->toBe(0)
        ->and($parsed->rows[0]->requestNumber)->toBe('BRPP525001B2D2P')
        ->and($parsed->rows[0]->sourceSurname)->toBe('Anu Ahmed')
        ->and($parsed->rows[0]->sourceGivenname)->toBe('Abato');
});

it('detects the table format after the configured start marker', function () {
    $parser = new PassportPdfParser();

    $text = <<<'TEXT'
Cover sheet
Application Number instructions for a different document
START HERE
No.    NAME    F. NAME    G.F. NAME    REQUEST_No.
1      ABAS    JALETA     ERESO         AIL9610825
TEXT;

    $parsed = $parser->parse($text, 'START HERE');

    expect($parsed->detectedFormat)->toBe(PassportPdfSourceFormat::LegacyFiveColumn)
        ->and($parsed->rows)->toHaveCount(1)
        ->and($parsed->rows[0]->requestNumber)->toBe('AIL9610825');
});
