<?php

use App\Domain\Passport\Models\PassportImportBatch;
use App\Jobs\PDFToSQLiteJob;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;

use function Pest\Laravel\getJson;
use function Pest\Laravel\post;

beforeEach(function () {
    Permission::findOrCreate('upload-files', 'web');
});

it('creates an import batch and dispatches the processing job', function () {
    Storage::fake('public');
    Queue::fake();

    $user = User::factory()->create();
    $user->givePermissionTo('upload-files');
    $token = $user->createToken('testsuite')->plainTextToken;

    $response = post('/api/v1/admin/pdf-to-sqlite', [
        'pdf_file' => UploadedFile::fake()->create('legacy.pdf', 64, 'application/pdf'),
        'date' => '2026-03-26',
        'location' => 'ICS branch office, Jimma',
        'linesToSkip' => 'REQUEST_No.',
    ], [
        'Accept' => 'application/json',
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertStatus(202)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.status', 'queued')
        ->assertJsonStructure([
            'data' => ['path', 'batch_id', 'status', 'status_url'],
        ]);

    $batch = PassportImportBatch::query()->firstOrFail();

    expect($batch->start_after_text)->toBe('REQUEST_No.')
        ->and($batch->location)->toBe('ICS branch office, Jimma');

    Queue::assertPushed(PDFToSQLiteJob::class, fn (PDFToSQLiteJob $job) => true);
});

it('returns batch status for admins with upload permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('upload-files');
    $token = $user->createToken('testsuite')->plainTextToken;

    $batch = PassportImportBatch::query()->create([
        'status' => 'processing',
        'file_path' => 'pdfs/legacy.pdf',
        'original_filename' => 'legacy.pdf',
        'source_format' => 'legacy_5col',
        'date_of_publish' => '2026-03-26',
        'location' => 'Addis Ababa',
        'start_after_text' => 'REQUEST_No.',
        'rows_total' => 10,
        'rows_inserted' => 8,
        'rows_updated' => 1,
        'rows_skipped' => 1,
        'rows_failed' => 0,
        'created_by' => $user->id,
    ]);

    $response = getJson('/api/v1/admin/passport-imports/'.$batch->id, [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.id', $batch->id)
        ->assertJsonPath('data.status', 'processing')
        ->assertJsonPath('data.rows_inserted', 8)
        ->assertJsonPath('data.source_format', 'legacy_5col');
});
