<?php

namespace App\Http\Controllers;

use App\Domain\Passport\Enums\PassportImportBatchStatus;
use App\Domain\Passport\Models\PassportImportBatch;
use App\Http\Requests\Passport\StorePassportImportRequest;
use App\Jobs\PDFToSQLiteJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class PDFToSQLiteController extends Controller
{
    public function store(StorePassportImportRequest $request)
    {
        try {
            $validated = $request->validated();

            $path = $request->file('pdf_file')->store('pdfs', 'public');
            if (! $path) {
                throw new \Exception('Failed to store the file.');
            }

            $filePath = storage_path('app/public/pdfs/' . basename($path));
            Log::info("File stored at: {$filePath}");

            $batch = PassportImportBatch::query()->create([
                'status' => PassportImportBatchStatus::Queued,
                'file_path' => $path,
                'original_filename' => $request->file('pdf_file')->getClientOriginalName(),
                'source_format' => $validated['format'],
                'date_of_publish' => $validated['date'],
                'location' => $validated['location'],
                'start_after_text' => $validated['start_after_text'],
                'created_by' => $request->user()?->id,
            ]);

            dispatch(new PDFToSQLiteJob($batch->id));
            Log::info("Job dispatched successfully");

            return Redirect::to('/')->with('success', 'PDF uploaded and processing started. Batch #'.$batch->id);
        } catch (\Exception $e) {
            Log::error('PDF upload failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return Redirect::back()->withErrors(['error' => 'The pdf file failed to upload.']);
        }
    }

    public function create()
    {
        return view('pdf-store');
    }
}

