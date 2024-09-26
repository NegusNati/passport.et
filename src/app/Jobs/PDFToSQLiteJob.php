<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Smalot\PdfParser\Parser;


class PDFToSQLiteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $filePath;
    private $records;
    private $date;
    private $linesToSkip;
    private $location;

    /**
     * Create a new job instance.
     *
     * @param string $pdfPath
     * @return void
     */
    public function __construct($filePath, $date,  $location, $linesToSkip)
    {
        $this->filePath = $filePath;
        $this->date = $date;
        $this->linesToSkip = $linesToSkip;
        $this->location = $location;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {



        try {
            Log::info("In Dispatch 2   ???");
            $parser = new Parser();
            $pdf = $parser->parseFile($this->filePath);
            Log::info("Got content");

            $text = $pdf->getText();
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
        Log::info("passed the location check");

        $records = [];
        $i = 0;

        $keyword = $this->linesToSkip;
        $lines = explode("\n", $text);
        $startParsing = false;

        foreach ($lines as $line) {
            if (!$startParsing) {
                if (strpos($line, $keyword) !== false) {
                    $startParsing = true;
                    continue;
                }
            } else {
                // parse the line as data
                $values = explode(" ", trim($line));
                $record = [
                    'no' => null,
                    'firstName' => null,
                    'middleName' => null,
                    'lastName' => null,
                    'requestNumber' => null,
                    'dateOfPublish' => $this->date,
                    'location' => $this->location,
                    'created_at' => now(),
                ];

                foreach ($values as $index => $value) {
                    switch ($index) {
                        case 0: {

                                $record['no'] = trim($value) ?? ' ';
                                break;
                            }
                        case 1: {

                                $record['firstName'] = trim($value)  ?? ' ';
                                break;
                            }
                        case 2:
                            $record['middleName'] = trim($value)  ?? ' ';
                            break;
                        case 3:
                            $record['lastName'] = trim($value) ?? ' ';
                            break;
                        case 4:
                            $record['requestNumber'] = trim($value);
                            break;
                        default:
                            // Set remaining values to an empty string
                            $record['no'] = $record['no'] ?? ' ';
                            $record['firstName'] = $record['firstName'] ?? ' ';
                            $record['middleName'] = $record['middleName'] ?? ' ';
                            $record['lastName'] = $record['lastName'] ?? ' ';
                            $record['requestNumber'] = $record['requestNumber'] ?? ' ';
                            break;
                    }
                }

                $records[] = $record;
            }
        }



        Log::info("Before the insert");
        // $resp = DB::table('p_d_f_to_s_q_lites')->insert($records);

        $chunks = array_chunk($records, 20); // spliting the data into chunks of 20 rows each
        foreach ($chunks as $chunk) {
            DB::table('p_d_f_to_s_q_lites')->insertOrIgnore($chunk);
        }
        Log::info("After the insert");
    }
}
