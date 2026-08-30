<?php

namespace App\Http\Requests\Passport;

use App\Domain\Passport\Enums\PassportPdfSourceFormat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePassportImportRequest extends FormRequest
{
    private const MAX_UPLOAD_KB = 51200;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $startAfterText = $this->input('start_after_text', $this->input('linesToSkip'));

        $this->merge([
            'start_after_text' => is_string($startAfterText) ? trim($startAfterText) : $startAfterText,
            'format' => $this->input('format', PassportPdfSourceFormat::Auto->value),
        ]);
    }

    public function rules(): array
    {
        return [
            'pdf_file' => ['required', 'file', 'mimes:pdf', 'max:'.self::MAX_UPLOAD_KB],
            'date' => ['required', 'date'],
            'location' => ['required', 'string', 'max:255'],
            'start_after_text' => ['nullable', 'string', 'max:255'],
            'linesToSkip' => ['nullable', 'string', 'max:255'],
            'format' => ['nullable', Rule::enum(PassportPdfSourceFormat::class)],
        ];
    }
}
