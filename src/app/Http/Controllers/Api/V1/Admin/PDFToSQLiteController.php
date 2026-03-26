<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Passport\Models\PassportImportBatch;
use App\Domain\Passport\Enums\PassportImportBatchStatus;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Passport\StorePassportImportRequest;
use App\Jobs\PDFToSQLiteJob;
use Illuminate\Support\Facades\Log;

class PDFToSQLiteController extends ApiController
{
    public function store(StorePassportImportRequest $request)
    {
        try {
            $validated = $request->validated();

            $uploadedFile = $request->file('pdf_file');
            $path = $uploadedFile?->store('pdfs', 'public');

            if (! $path) {
                throw new \RuntimeException('Failed to store the file.');
            }

            $filePath = storage_path('app/public/'.$path);
            Log::info("File stored at: {$filePath}");

            $batch = PassportImportBatch::query()->create([
                'status' => PassportImportBatchStatus::Queued,
                'file_path' => $path,
                'original_filename' => $uploadedFile?->getClientOriginalName() ?? basename($path),
                'source_format' => $validated['format'],
                'date_of_publish' => $validated['date'],
                'location' => $validated['location'],
                'start_after_text' => $validated['start_after_text'],
                'created_by' => $request->user()?->id,
            ]);

            dispatch(new PDFToSQLiteJob($batch->id));

            Log::info('PDF to SQLite job dispatched successfully.');

            return $this->respond([
                'status' => 'success',
                'message' => 'PDF uploaded and processing started.',
                'data' => [
                    'path' => $path,
                    'batch_id' => $batch->id,
                    'status' => $batch->status->value,
                    'status_url' => route('api.v1.admin.passport-imports.show', ['passportImportBatch' => $batch->id], false),
                ],
            ], 202);
        } catch (\Throwable $e) {
            Log::error('PDF upload failed: '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->respond([
                'status' => 'error',
                'code' => 'pdf_upload_failed',
                'message' => 'The pdf file failed to upload.',
            ], 422);
        }
    }

    public function create()
    {
        return $this->respond([
            'message' => 'Use POST /api/v1/admin/pdf-to-sqlite to upload a PDF for processing.',
            'constraints' => [
                'pdf_file' => 'required PDF file up to 10MB',
                'date' => 'required date (YYYY-MM-DD)',
                'location' => 'required string',
                'start_after_text' => 'required string matched before row parsing starts',
                'linesToSkip' => 'legacy alias for start_after_text',
                'format' => 'optional: auto | legacy_5col | application_4col',
            ],
        ]);
    }
}
