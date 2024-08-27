<?php

namespace App\Http\Controllers;

use App\Models\PDFToSQLite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Spatie\PdfToText\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TelegramPDFController extends Controller
{
    public function setWebhook()
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');
        $webhookUrl = 'https://lmuc2nrlq1.sharedwithexpose.com'. '/telegram/webhook';

        $response = Http::get("https://api.telegram.org/bot{$botToken}/setWebhook", [
            'url' => $webhookUrl,
        ]);

        if ($response->successful()) {
            return response()->json(['message' => 'Webhook set successfully']);
        } else {
            return response()->json(['message' => 'Failed to set webhook', 'details' => $response->body()], 500);
        }
    }

    public function handleWebhook(Request $request)
    {

        $update = $request->all();

        if (isset($update['message']['document'])) {
            $document = $update['message']['document'];
            if ($document['mime_type'] == 'application/pdf') {
                $file_id = $document['file_id'];
                $this->downloadAndProcessPDF($file_id);
            }
        }
        Log::info('Webhook received: ', $update);
        return response()->json(['status' => 'ok']);
    }

    private function downloadAndProcessPDF($file_id)
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');
        $fileResponse = Http::get("https://api.telegram.org/bot{$botToken}/getFile?file_id={$file_id}");
        $filePath = $fileResponse->json()['result']['file_path'];
        $fileUrl = "https://api.telegram.org/file/bot{$botToken}/{$filePath}";

        $fileContent = Http::get($fileUrl)->body();

        $tempFilePath = storage_path('app/public/downloaded.pdf');
        file_put_contents($tempFilePath, $fileContent);

        // $this->convertPDFToSQLite($tempFilePath);


        //to use our last converter

        // PDFToSQLiteController::convertTelegramPDFToSQLite( $tempFilePath); Depreciated

        Log::info('PDF path sent ', $tempFilePath);

    }

    private function convertPDFToSQLite($pdfFilePath)
    {
        $text = (new Pdf())->setPdf($pdfFilePath)->text();

        $sqliteFilePath = storage_path('app/public/converted.sqlite');
        $db = new \SQLite3($sqliteFilePath);
        $db->exec('CREATE TABLE IF NOT EXISTS pdf_data (id INTEGER PRIMARY KEY, content TEXT)');
        $stmt = $db->prepare('INSERT INTO pdf_data (content) VALUES (:content)');
        $stmt->bindValue(':content', $text, SQLITE3_TEXT);
        $stmt->execute();

        $this->uploadToSite($sqliteFilePath);
    }

    private function uploadToSite($filePath)
    {
        $uploadUrl = env('UPLOAD_SITE_URL');
        $response = Http::attach(
            'file', file_get_contents($filePath), 'converted.sqlite'
        )->post($uploadUrl);

        if ($response->successful()) {
            Log::info('File uploaded successfully');
        } else {
            Log::error('Failed to upload file');
        }
    }
}
