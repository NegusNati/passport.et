<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PassportImportBatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status?->value ?? $this->status,
            'source_format' => $this->source_format?->value ?? $this->source_format,
            'original_filename' => $this->original_filename,
            'file_path' => $this->file_path,
            'date_of_publish' => $this->date_of_publish?->toDateString(),
            'location' => $this->location,
            'start_after_text' => $this->start_after_text,
            'rows_total' => (int) $this->rows_total,
            'rows_inserted' => (int) $this->rows_inserted,
            'rows_updated' => (int) $this->rows_updated,
            'rows_skipped' => (int) $this->rows_skipped,
            'rows_failed' => (int) $this->rows_failed,
            'error_message' => $this->error_message,
            'started_at' => optional($this->started_at)->toIso8601String(),
            'finished_at' => optional($this->finished_at)->toIso8601String(),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
