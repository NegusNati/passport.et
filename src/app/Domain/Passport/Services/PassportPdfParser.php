<?php

namespace App\Domain\Passport\Services;

use App\Domain\Passport\Data\ParsedPassportImport;
use App\Domain\Passport\Data\PassportImportRow;
use App\Domain\Passport\Enums\PassportPdfSourceFormat;
use Illuminate\Support\Str;
use RuntimeException;

class PassportPdfParser
{
    public function parse(
        string $text,
        ?string $startAfterText = null,
        PassportPdfSourceFormat $sourceFormat = PassportPdfSourceFormat::Auto,
    ): ParsedPassportImport {
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $lines = array_values(array_map(static fn (string $line): string => rtrim($line), $lines));
        $lines = $this->sliceAfterMarker($lines, $startAfterText);

        $detectedFormat = $sourceFormat === PassportPdfSourceFormat::Auto
            ? $this->detectFormat($lines)
            : $sourceFormat;

        $dataLines = $this->sliceAfterHeader($lines, $detectedFormat);

        return $this->parseRows($dataLines, $detectedFormat);
    }

    /**
     * @param array<int, string> $lines
     */
    private function detectFormat(array $lines): PassportPdfSourceFormat
    {
        foreach ($lines as $line) {
            if ($this->isHeaderLine($line, PassportPdfSourceFormat::ApplicationFourColumn)) {
                return PassportPdfSourceFormat::ApplicationFourColumn;
            }

            if ($this->isHeaderLine($line, PassportPdfSourceFormat::LegacyFiveColumn)) {
                return PassportPdfSourceFormat::LegacyFiveColumn;
            }
        }

        $haystack = Str::lower(implode("\n", $lines));

        if (str_contains($haystack, 'application number') && str_contains($haystack, "applicant's")) {
            return PassportPdfSourceFormat::ApplicationFourColumn;
        }

        if (str_contains($haystack, 'request_no') || str_contains($haystack, 'g.f. name')) {
            return PassportPdfSourceFormat::LegacyFiveColumn;
        }

        throw new RuntimeException('Unable to detect the PDF table format.');
    }

    /**
     * @param array<int, string> $lines
     */
    private function parseRows(array $lines, PassportPdfSourceFormat $format): ParsedPassportImport
    {
        $rows = [];
        $failedRows = 0;
        $skippedRows = 0;
        $currentRow = null;

        foreach ($lines as $line) {
            $rawLine = rtrim($line);
            $trimmed = trim($rawLine);

            if ($trimmed === '') {
                continue;
            }

            if ($this->isHeaderLine($trimmed, $format)) {
                continue;
            }

            if ($this->looksLikeRowStart($trimmed)) {
                if ($currentRow !== null) {
                    $parsedRow = $this->parseRow($currentRow, $format);

                    if ($parsedRow instanceof PassportImportRow) {
                        $rows[] = $parsedRow;
                    } else {
                        $failedRows++;
                    }
                }

                $currentRow = $rawLine;

                continue;
            }

            if ($currentRow !== null) {
                if (
                    $this->parseRow($currentRow, $format) instanceof PassportImportRow
                    && ! $this->looksLikeContinuationFragment($rawLine)
                ) {
                    $skippedRows++;

                    continue;
                }

                $currentRow .= "\n".$rawLine;
            } else {
                $skippedRows++;
            }
        }

        if ($currentRow !== null) {
            $parsedRow = $this->parseRow($currentRow, $format);

            if ($parsedRow instanceof PassportImportRow) {
                $rows[] = $parsedRow;
            } else {
                $failedRows++;
            }
        }

        return new ParsedPassportImport(
            detectedFormat: $format,
            rows: $rows,
            skippedRows: $skippedRows,
            failedRows: $failedRows,
        );
    }

    private function parseRow(string $line, PassportPdfSourceFormat $format): ?PassportImportRow
    {
        return match ($format) {
            PassportPdfSourceFormat::LegacyFiveColumn => $this->parseLegacyRow($line),
            PassportPdfSourceFormat::ApplicationFourColumn => $this->parseApplicationRow($line),
            default => null,
        };
    }

    private function parseLegacyRow(string $line): ?PassportImportRow
    {
        $segments = $this->splitColumns($line);

        if (count($segments) < 5) {
            $segments = $this->splitWhitespaceFallback($line);

            if (count($segments) < 5) {
                return null;
            }
        }

        $number = $this->parseNumber(array_shift($segments));
        $requestNumber = array_pop($segments);

        return PassportImportRow::legacy(
            number: $number,
            firstName: $segments[0] ?? null,
            middleName: $segments[1] ?? null,
            lastName: ! empty($segments) ? implode(' ', array_slice($segments, 2)) ?: ($segments[2] ?? null) : null,
            requestNumber: $requestNumber ?? '',
        );
    }

    private function parseApplicationRow(string $line): ?PassportImportRow
    {
        $segments = $this->splitColumns($line);

        if (count($segments) < 4) {
            $segments = $this->splitWhitespaceFallback($line);

            if (count($segments) < 4) {
                return null;
            }
        }

        $number = $this->parseNumber(array_shift($segments));
        $applicationNumber = array_shift($segments);
        $givenname = array_pop($segments);
        $surname = $segments !== [] ? implode(' ', $segments) : null;

        return PassportImportRow::application(
            number: $number,
            applicationNumber: $applicationNumber ?? '',
            sourceSurname: $surname,
            sourceGivenname: $givenname,
        );
    }

    /**
     * @param array<int, string> $lines
     * @return array<int, string>
     */
    private function sliceAfterMarker(array $lines, ?string $startAfterText): array
    {
        $marker = trim((string) $startAfterText);

        if ($marker === '') {
            return $lines;
        }

        foreach ($lines as $index => $line) {
            if (stripos($line, $marker) !== false) {
                return array_slice($lines, $index);
            }
        }

        return $lines;
    }

    /**
     * @param array<int, string> $lines
     * @return array<int, string>
     */
    private function sliceAfterHeader(array $lines, PassportPdfSourceFormat $format): array
    {
        foreach ($lines as $index => $line) {
            if ($this->isHeaderLine($line, $format)) {
                return array_slice($lines, $index + 1);
            }
        }

        foreach ($lines as $index => $line) {
            if ($this->looksLikeRowStart($line)) {
                return array_slice($lines, $index);
            }
        }

        return [];
    }

    private function isHeaderLine(string $line, PassportPdfSourceFormat $format): bool
    {
        $normalizedLine = Str::of($line)
            ->replace("\t", ' ')
            ->squish()
            ->lower()
            ->value();

        return match ($format) {
            PassportPdfSourceFormat::LegacyFiveColumn => str_contains($normalizedLine, 'name')
                && str_contains($normalizedLine, 'f. name')
                && str_contains($normalizedLine, 'g.f. name')
                && (str_contains($normalizedLine, 'request_no') || str_contains($normalizedLine, 'request no')),
            PassportPdfSourceFormat::ApplicationFourColumn => str_contains($normalizedLine, 'application number')
                && str_contains($normalizedLine, "applicant's surname")
                && str_contains($normalizedLine, "applicant's givenname"),
            default => false,
        };
    }

    private function looksLikeRowStart(string $line): bool
    {
        return preg_match('/^\s*\d+\b/u', $line) === 1;
    }

    private function looksLikeContinuationFragment(string $line): bool
    {
        return preg_match('/^\s+\S/u', $line) === 1 || preg_match('/\s{2,}\S/u', $line) === 1;
    }

    /**
     * @return array<int, string>
     */
    private function splitColumns(string $line): array
    {
        $normalized = preg_replace('/\R+/u', '  ', $line) ?? $line;
        $segments = preg_split('/\s{2,}/u', trim($normalized)) ?: [];

        return array_values(array_filter(
            array_map(static fn (string $segment): string => Str::squish($segment), $segments),
            static fn (string $segment): bool => $segment !== ''
        ));
    }

    /**
     * @return array<int, string>
     */
    private function splitWhitespaceFallback(string $line): array
    {
        $normalized = preg_replace('/\R+/u', ' ', $line) ?? $line;
        $segments = preg_split('/\s+/u', trim($normalized)) ?: [];

        return array_values(array_filter(
            array_map(static fn (string $segment): string => Str::squish($segment), $segments),
            static fn (string $segment): bool => $segment !== ''
        ));
    }

    private function parseNumber(?string $value): ?int
    {
        if ($value === null) {
            return null;
        }

        return preg_match('/\d+/u', $value, $matches) === 1 ? (int) $matches[0] : null;
    }
}
