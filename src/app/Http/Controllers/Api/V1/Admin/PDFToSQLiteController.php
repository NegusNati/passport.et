<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Jobs\PDFToSQLiteJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PDFToSQLiteController extends ApiController
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'pdf_file' => 'required|file|mimes:pdf|max:10240',
                'date' => 'required|date',
                'location' => 'required',
                'linesToSkip' => 'required',
            ]);

            $uploadedFile = $request->file('pdf_file');
            $path = $uploadedFile?->store('pdfs', 'public');

            if (! $path) {
                throw new \RuntimeException('Failed to store the file.');
            }

            $filePath = storage_path('app/public/'.$path);
            Log::info("File stored at: {$filePath}");

            dispatch(new PDFToSQLiteJob(
                $filePath,
                $validated['date'],
                $validated['location'],
                $validated['linesToSkip']
            ));

            Log::info('PDF to SQLite job dispatched successfully.');

            return $this->respond([
                'status' => 'success',
                'message' => 'PDF uploaded and processing started.',
                'data' => [
                    'path' => $path,
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
                'linesToSkip' => 'required (integer or numeric)',
            ],
        ]);
    }
}
