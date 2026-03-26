<?php

namespace App\Domain\Passport\Models;

use App\Domain\Passport\Enums\PassportImportBatchStatus;
use App\Domain\Passport\Enums\PassportPdfSourceFormat;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PassportImportBatch extends Model
{
    protected $fillable = [
        'status',
        'file_path',
        'original_filename',
        'source_format',
        'date_of_publish',
        'location',
        'start_after_text',
        'rows_total',
        'rows_inserted',
        'rows_updated',
        'rows_skipped',
        'rows_failed',
        'error_message',
        'started_at',
        'finished_at',
        'created_by',
    ];

    protected $casts = [
        'status' => PassportImportBatchStatus::class,
        'source_format' => PassportPdfSourceFormat::class,
        'date_of_publish' => 'date',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function passports(): HasMany
    {
        return $this->hasMany(Passport::class, 'importBatchId');
    }
}
