<?php

use App\Domain\Passport\Enums\PassportImportBatchStatus;
use App\Domain\Passport\Enums\PassportPdfSourceFormat;
use App\Domain\Passport\Models\Passport;
use App\Domain\Passport\Models\PassportImportBatch;
use App\Domain\Passport\Services\PassportImportService;
use App\Domain\Passport\Services\PassportPdfParser;
use App\Domain\Passport\Services\PassportPdfTextExtractor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

it('processes a four-column import batch and writes compatibility fields', function () {
    Storage::fake('public');
    Storage::disk('public')->put('pdfs/application.pdf', 'stub');

    $batch = PassportImportBatch::query()->create([
        'status' => PassportImportBatchStatus::Queued,
        'file_path' => 'pdfs/application.pdf',
        'original_filename' => 'application.pdf',
        'source_format' => PassportPdfSourceFormat::Auto,
        'date_of_publish' => '2026-03-26',
        'location' => 'ICS branch office, Jimma',
        'start_after_text' => 'Application Number',
    ]);

    $extractor = \Mockery::mock(PassportPdfTextExtractor::class);
    $extractor->shouldReceive('extract')
        ->once()
        ->andReturn(<<<'TEXT'
No.    Application Number    Applicant's Surname    Applicant's Givenname
1      BRPP525001B2D2P       Anu Ahmed              Abato
TEXT);

    $service = new PassportImportService($extractor, new PassportPdfParser(), Cache::store());
    $processed = $service->process($batch->fresh());

    $passport = Passport::query()->where('requestNumber', 'BRPP525001B2D2P')->firstOrFail();

    expect($processed->status)->toBe(PassportImportBatchStatus::Completed)
        ->and($processed->rows_inserted)->toBe(1)
        ->and($processed->rows_updated)->toBe(0)
        ->and($passport->applicationNumber)->toBe('BRPP525001B2D2P')
        ->and($passport->firstName)->toBe('Abato')
        ->and($passport->middleName)->toBe('Anu')
        ->and($passport->lastName)->toBe('Ahmed')
        ->and($passport->sourceSurname)->toBe('Anu Ahmed');
});

it('upserts repeated request numbers and marks the later batch as updates', function () {
    Storage::fake('public');
    Storage::disk('public')->put('pdfs/legacy-a.pdf', 'stub');
    Storage::disk('public')->put('pdfs/legacy-b.pdf', 'stub');

    $extractor = \Mockery::mock(PassportPdfTextExtractor::class);
    $extractor->shouldReceive('extract')
        ->twice()
        ->andReturn(
            <<<'TEXT'
No.    NAME    F. NAME    G.F. NAME    REQUEST_No.
1      ABAS    JALETA     ERESO         AIL9610825
TEXT,
            <<<'TEXT'
No.    NAME    F. NAME    G.F. NAME    REQUEST_No.
1      ABAS    JALETA     UPDATED       AIL9610825
TEXT,
        );

    $service = new PassportImportService($extractor, new PassportPdfParser(), Cache::store());

    $firstBatch = PassportImportBatch::query()->create([
        'status' => PassportImportBatchStatus::Queued,
        'file_path' => 'pdfs/legacy-a.pdf',
        'original_filename' => 'legacy-a.pdf',
        'source_format' => PassportPdfSourceFormat::LegacyFiveColumn,
        'date_of_publish' => '2026-03-26',
        'location' => 'Addis Ababa',
        'start_after_text' => 'REQUEST_No.',
    ]);

    $secondBatch = PassportImportBatch::query()->create([
        'status' => PassportImportBatchStatus::Queued,
        'file_path' => 'pdfs/legacy-b.pdf',
        'original_filename' => 'legacy-b.pdf',
        'source_format' => PassportPdfSourceFormat::LegacyFiveColumn,
        'date_of_publish' => '2026-03-27',
        'location' => 'Dire Dawa',
        'start_after_text' => 'REQUEST_No.',
    ]);

    $service->process($firstBatch);
    $processedSecondBatch = $service->process($secondBatch);

    $passport = Passport::query()->where('requestNumber', 'AIL9610825')->firstOrFail();

    expect($processedSecondBatch->rows_inserted)->toBe(0)
        ->and($processedSecondBatch->rows_updated)->toBe(1)
        ->and($passport->lastName)->toBe('UPDATED')
        ->and($passport->location)->toBe('Dire Dawa')
        ->and($passport->importBatchId)->toBe($secondBatch->id);
});

it('marks the batch as failed when extraction or parsing throws', function () {
    Storage::fake('public');
    Storage::disk('public')->put('pdfs/bad.pdf', 'stub');

    $batch = PassportImportBatch::query()->create([
        'status' => PassportImportBatchStatus::Queued,
        'file_path' => 'pdfs/bad.pdf',
        'original_filename' => 'bad.pdf',
        'source_format' => PassportPdfSourceFormat::Auto,
        'date_of_publish' => '2026-03-26',
        'location' => 'Addis Ababa',
        'start_after_text' => 'HEADER',
    ]);

    $extractor = \Mockery::mock(PassportPdfTextExtractor::class);
    $extractor->shouldReceive('extract')
        ->once()
        ->andThrow(new RuntimeException('Parser exploded'));

    $service = new PassportImportService($extractor, new PassportPdfParser(), Cache::store());

    expect(fn () => $service->process($batch))->toThrow(RuntimeException::class, 'Parser exploded');

    expect($batch->fresh()->status)->toBe(PassportImportBatchStatus::Failed)
        ->and($batch->fresh()->error_message)->toBe('Parser exploded');
});

it('marks the batch as failed when no passport rows can be parsed', function () {
    Storage::fake('public');
    Storage::disk('public')->put('pdfs/empty.pdf', 'stub');

    $batch = PassportImportBatch::query()->create([
        'status' => PassportImportBatchStatus::Queued,
        'file_path' => 'pdfs/empty.pdf',
        'original_filename' => 'empty.pdf',
        'source_format' => PassportPdfSourceFormat::ApplicationFourColumn,
        'date_of_publish' => '2026-03-26',
        'location' => 'Addis Ababa',
        'start_after_text' => 'Application Number',
    ]);

    $extractor = \Mockery::mock(PassportPdfTextExtractor::class);
    $extractor->shouldReceive('extract')
        ->once()
        ->andReturn(<<<'TEXT'
No.    Application Number    Applicant's Surname    Applicant's Givenname
1      BROKEN
TEXT);

    $service = new PassportImportService($extractor, new PassportPdfParser(), Cache::store());

    expect(fn () => $service->process($batch))
        ->toThrow(RuntimeException::class, 'No passport rows could be parsed');

    expect($batch->fresh()->status)->toBe(PassportImportBatchStatus::Failed)
        ->and($batch->fresh()->error_message)->toContain('No passport rows could be parsed');
});
