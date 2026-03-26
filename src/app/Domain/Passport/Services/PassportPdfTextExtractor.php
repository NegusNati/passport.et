<?php

namespace App\Domain\Passport\Services;

use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser as SmalotParser;
use Spatie\PdfToText\Pdf as PdfToText;
use Symfony\Component\Process\ExecutableFinder;
use Throwable;

class PassportPdfTextExtractor
{
    public function __construct(
        private readonly SmalotParser $smalotParser = new SmalotParser(),
    ) {
    }

    public function extract(string $absoluteFilePath): string
    {
        $layoutText = $this->extractWithPdftotext($absoluteFilePath);

        if ($layoutText !== null && trim($layoutText) !== '') {
            return $layoutText;
        }

        return trim($this->smalotParser->parseFile($absoluteFilePath)->getText());
    }

    private function extractWithPdftotext(string $absoluteFilePath): ?string
    {
        $binary = $this->resolvePdftotextBinary();

        if ($binary === null) {
            return null;
        }

        try {
            return PdfToText::getText($absoluteFilePath, $binary, ['layout']);
        } catch (Throwable $exception) {
            Log::warning('pdftotext extraction failed, falling back to smalot/pdfparser.', [
                'file' => $absoluteFilePath,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function resolvePdftotextBinary(): ?string
    {
        $configuredBinary = env('PDFTOTEXT_BIN');

        if (is_string($configuredBinary) && $configuredBinary !== '' && is_executable($configuredBinary)) {
            return $configuredBinary;
        }

        return (new ExecutableFinder())->find('pdftotext');
    }
}
