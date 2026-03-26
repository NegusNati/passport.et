<?php

namespace App\Domain\Passport\Data;

use App\Domain\Passport\Enums\PassportPdfSourceFormat;
use App\Domain\Passport\Models\PassportImportBatch;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

readonly class PassportImportRow
{
    public function __construct(
        public ?int $number,
        public string $requestNumber,
        public ?string $firstName,
        public ?string $middleName,
        public ?string $lastName,
        public ?string $applicationNumber,
        public ?string $sourceSurname,
        public ?string $sourceGivenname,
        public PassportPdfSourceFormat $sourceFormat,
    ) {
    }

    public static function legacy(
        ?int $number,
        ?string $firstName,
        ?string $middleName,
        ?string $lastName,
        string $requestNumber,
    ): ?self {
        $normalizedRequestNumber = self::normalizeIdentifier($requestNumber);

        if ($normalizedRequestNumber === null) {
            return null;
        }

        return new self(
            number: $number,
            requestNumber: $normalizedRequestNumber,
            firstName: self::normalizeCell($firstName),
            middleName: self::normalizeCell($middleName),
            lastName: self::normalizeCell($lastName),
            applicationNumber: null,
            sourceSurname: null,
            sourceGivenname: null,
            sourceFormat: PassportPdfSourceFormat::LegacyFiveColumn,
        );
    }

    public static function application(
        ?int $number,
        string $applicationNumber,
        ?string $sourceSurname,
        ?string $sourceGivenname,
    ): ?self {
        $normalizedApplicationNumber = self::normalizeIdentifier($applicationNumber);

        if ($normalizedApplicationNumber === null) {
            return null;
        }

        $normalizedSurname = self::normalizeCell($sourceSurname);
        $normalizedGivenname = self::normalizeCell($sourceGivenname);
        [$middleName, $lastName] = self::splitSurname($normalizedSurname);

        return new self(
            number: $number,
            requestNumber: $normalizedApplicationNumber,
            firstName: $normalizedGivenname,
            middleName: $middleName,
            lastName: $lastName,
            applicationNumber: $normalizedApplicationNumber,
            sourceSurname: $normalizedSurname,
            sourceGivenname: $normalizedGivenname,
            sourceFormat: PassportPdfSourceFormat::ApplicationFourColumn,
        );
    }

    public function toDatabaseRow(PassportImportBatch $batch, Carbon $timestamp): array
    {
        return [
            'no' => $this->number,
            'firstName' => $this->firstName,
            'middleName' => $this->middleName,
            'lastName' => $this->lastName,
            'requestNumber' => $this->requestNumber,
            'applicationNumber' => $this->applicationNumber,
            'sourceSurname' => $this->sourceSurname,
            'sourceGivenname' => $this->sourceGivenname,
            'sourceFormat' => $this->sourceFormat->value,
            'importBatchId' => $batch->getKey(),
            'location' => $batch->location,
            'dateOfPublish' => $batch->date_of_publish?->toDateString(),
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }

    private static function splitSurname(?string $surname): array
    {
        $tokens = preg_split('/\s+/u', $surname ?? '', -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($tokens === []) {
            return [null, null];
        }

        if (count($tokens) === 1) {
            return [null, $tokens[0]];
        }

        $middleName = array_shift($tokens);

        return [$middleName, implode(' ', $tokens)];
    }

    private static function normalizeCell(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = Str::squish($value);

        return $value !== '' ? $value : null;
    }

    private static function normalizeIdentifier(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = Str::upper(str_replace([' ', '-'], '', Str::squish($value)));

        return $value !== '' ? $value : null;
    }
}
