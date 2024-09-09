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

        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf',
            'date' => 'required|date',
            'location' => 'required',
            'linesToSkip' => 'required',
        ]);

        $path = $request->pdf_file->store('pdfs');
        $filePath = storage_path('app/public/pdfs/' . basename($path));
        // $filePath = storage_path('app/pdfs/' . basename($path));
        Log::info("Attempting to read file path: {$filePath}");
        // PDFToSQLiteJob::dispatch($filePath);
        dispatch(new PDFToSQLiteJob($filePath , $request->date, $request->location,$request->linesToSkip));
        Log::info("After the dispatch");


        return Redirect::to('/');


        // $request->validate([
        //     'pdf_file' => 'required|file|mimes:pdf',
        // ]);

        // $pdfPath = $request->file('pdf_file')->store('pdfs');

        // // Dispatch the PDFToSQLiteJob
        // PDFToSQLiteJob::dispatch($pdfPath);

        // return response()->json(['status' => 'success']);
    }





    public function create()
    {
        return view('pdf-store');
    }

    //     try {
    //         $parser = new Parser();
    //         $pdf = $parser->parseFile('C:\Users\natna\Documents\genbot_28.pdf');
    //         $text = $pdf->getText();
    //     } catch (\Exception $e) {
    //         // Handle the exception, e.g., log the error, return an error response
    //         return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    //     }

    // $records = [];
    // $i = 0;
    // foreach (explode("\n", $text) as $line) {
    //     if (!empty($line)) { // Skip empty lines
    //         $i++;
    //         if ($i > 2) { // Skip the first two lines
    //             [$numberId, $firstName, $middleName, $lastName, $applicationNumber] = explode(" ", trim($line), 5);
    //             if (!empty($numberId) && !empty($firstName) && !empty($middleName) && !empty($lastName) && !empty($applicationNumber)) {
    //                 $id = $numberId;
    //                 $firstName = $firstName;
    //                 $middleName = $middleName;
    //                 $lastName = $lastName;
    //                 $applicationNumber = $applicationNumber;
    //                 $dateOfPublish = "04. JUN. 2024"; // Assuming all records have the same publish date
    //                 $records[] = compact('id', 'firstName', 'middleName', 'lastName', 'applicationNumber', 'dateOfPublish');

    //             } else {
    //                 Log::warning('Skipping line due to missing data: ' . $line);
    //             }
    //         }
    //     }
    // }

    // $resp = DB::table('p_d_f_to_s_q_lites')->insert($records);
    // dd($resp);
    // return response()->json(['status' => 'success']);
    // }






//Depreciated
//     public static function convertTelegramPDFToSQLite(String $pdfFilePath)
//     {
//        $path = $pdfFilePath;

//        $filePath = storage_path('app/public/pdfs/' . basename($path));
//        Log::info("Attempting to read file path: {$filePath}");
//        // PDFToSQLiteJob::dispatch($filePath);
//        dispatch(new PDFToSQLiteJob($filePath , date('Y-m-d')  , "Gotera, Addis Ababa", "Request_No" ));
//        Log::info("After the dispatch");


//        return Redirect::to('/');

//    }

}


