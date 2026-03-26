<?php

namespace App\Domain\Passport\Data;

use App\Domain\Passport\Enums\PassportPdfSourceFormat;

readonly class ParsedPassportImport
{
    /**
     * @param array<int, PassportImportRow> $rows
     */
    public function __construct(
        public PassportPdfSourceFormat $detectedFormat,
        public array $rows,
        public int $skippedRows = 0,
        public int $failedRows = 0,
    ) {
    }
}
