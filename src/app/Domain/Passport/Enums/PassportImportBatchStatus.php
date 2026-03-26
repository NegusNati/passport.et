<?php

namespace App\Domain\Passport\Enums;

enum PassportImportBatchStatus: string
{
    case Queued = 'queued';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
}
