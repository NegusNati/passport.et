<?php

namespace App\Domain\Passport\Services;

use App\Domain\Passport\Data\ParsedPassportImport;
use App\Domain\Passport\Models\PassportImportBatch;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class PassportImportService
{
    private const UPSERT_CHUNK_SIZE = 500;

    public function __construct(
        private readonly PassportPdfTextExtractor $textExtractor,
        private readonly PassportPdfParser $pdfParser,
        private readonly CacheRepository $cache,
    ) {
    }

    public function process(PassportImportBatch $batch): PassportImportBatch
    {
        $batch->forceFill([
            'status' => 'processing',
            'started_at' => now(),
            'finished_at' => null,
            'error_message' => null,
        ])->save();

        try {
            $absolutePath = Storage::disk('public')->path($batch->file_path);
            $text = $this->textExtractor->extract($absolutePath);
            $parsed = $this->pdfParser->parse(
                text: $text,
                startAfterText: $batch->start_after_text,
                sourceFormat: $batch->source_format,
            );

            if ($parsed->rows === []) {
                throw new RuntimeException(
                    sprintf(
                        'No passport rows could be parsed from the uploaded PDF. Failed rows: %d. Skipped rows: %d.',
                        $parsed->failedRows,
                        $parsed->skippedRows,
                    )
                );
            }

            $summary = $this->persistRows($batch, $parsed);

            $batch->forceFill([
                'status' => 'completed',
                'source_format' => $parsed->detectedFormat->value,
                'rows_total' => count($parsed->rows),
                'rows_inserted' => $summary['inserted'],
                'rows_updated' => $summary['updated'],
                'rows_skipped' => $parsed->skippedRows + $summary['duplicates'],
                'rows_failed' => $parsed->failedRows,
                'finished_at' => now(),
            ])->save();

            $this->cache->tags(['passports'])->flush();

            return $batch->refresh();
        } catch (Throwable $exception) {
            $this->markFailed($batch, $exception);

            throw $exception;
        }
    }

    public function markFailed(PassportImportBatch $batch, Throwable $exception): void
    {
        $batch->forceFill([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
            'finished_at' => now(),
        ])->save();
    }

    /**
     * @return array{inserted:int, updated:int, duplicates:int}
     */
    private function persistRows(PassportImportBatch $batch, ParsedPassportImport $parsed): array
    {
        $preparedRows = [];
        $seenRequestNumbers = [];
        $duplicateCount = 0;
        $timestamp = Carbon::now();

        foreach ($parsed->rows as $row) {
            if (isset($seenRequestNumbers[$row->requestNumber])) {
                $duplicateCount++;

                continue;
            }

            $seenRequestNumbers[$row->requestNumber] = true;
            $preparedRows[] = $row->toDatabaseRow($batch, $timestamp);
        }

        if ($preparedRows === []) {
            return ['inserted' => 0, 'updated' => 0, 'duplicates' => $duplicateCount];
        }

        $inserted = 0;
        $updated = 0;

        foreach (array_chunk($preparedRows, self::UPSERT_CHUNK_SIZE) as $chunk) {
            $requestNumbers = array_values(array_unique(array_column($chunk, 'requestNumber')));
            $existingRequestNumbers = DB::table('p_d_f_to_s_q_lites')
                ->whereIn('requestNumber', $requestNumbers)
                ->pluck('requestNumber')
                ->all();

            $existingLookup = array_fill_keys($existingRequestNumbers, true);

            foreach ($requestNumbers as $requestNumber) {
                if (isset($existingLookup[$requestNumber])) {
                    $updated++;
                } else {
                    $inserted++;
                }
            }

            DB::table('p_d_f_to_s_q_lites')->upsert(
                $chunk,
                ['requestNumber'],
                [
                    'no',
                    'firstName',
                    'middleName',
                    'lastName',
                    'applicationNumber',
                    'sourceSurname',
                    'sourceGivenname',
                    'sourceFormat',
                    'importBatchId',
                    'location',
                    'dateOfPublish',
                    'updated_at',
                ],
            );
        }

        return [
            'inserted' => $inserted,
            'updated' => $updated,
            'duplicates' => $duplicateCount,
        ];
    }
}
