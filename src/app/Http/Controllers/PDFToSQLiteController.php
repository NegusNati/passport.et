<?php

namespace App\Http\Controllers;

use App\Jobs\PDFToSQLiteJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Smalot\PdfParser\Parser;

class PDFToSQLiteController extends Controller
{

    public function store(Request $request)
    {


        try {
            $request->validate([
                'pdf_file' => 'required|file|mimes:pdf|max:10240', // 10MB max
                'date' => 'required|date',
                'location' => 'required',
                'linesToSkip' => 'required',
            ]);

            $path = $request->file('pdf_file')->store('pdfs', 'public');
            if (!$path) {
                throw new \Exception('Failed to store the file.');
            }

            $filePath = storage_path('app/public/pdfs/' . basename($path));
            Log::info("File stored at: {$filePath}");

            dispatch(new PDFToSQLiteJob($filePath, $request->date, $request->location, $request->linesToSkip));
            Log::info("Job dispatched successfully");

            return Redirect::to('/')->with('success', 'PDF uploaded and processing started.');
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


