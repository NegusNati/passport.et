<?php

namespace App\Jobs;

use App\Domain\Passport\Models\PassportImportBatch;
use App\Domain\Passport\Services\PassportImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PDFToSQLiteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;
    public array $backoff = [60, 300, 900];

    public function __construct(
        private readonly int $batchId,
    )
    {
        $this->onConnection('redis');
        $this->onQueue('imports');
    }

    public function handle(PassportImportService $importService): void
    {
        $batch = PassportImportBatch::query()->findOrFail($this->batchId);

        $importService->process($batch);
    }

    public function failed(\Throwable $exception): void
    {
        $batch = PassportImportBatch::query()->find($this->batchId);

        if (! $batch) {
            return;
        }

        app(PassportImportService::class)->markFailed($batch, $exception);
    }
}
