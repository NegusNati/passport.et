<?php

namespace App\Domain\Passport\Enums;

enum PassportPdfSourceFormat: string
{
    case Auto = 'auto';
    case LegacyFiveColumn = 'legacy_5col';
    case ApplicationFourColumn = 'application_4col';
}
