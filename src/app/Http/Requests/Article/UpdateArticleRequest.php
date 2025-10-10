<?php

namespace App\Http\Requests\Article;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null; // guarded elsewhere
    }

    public function rules(): array
    {
        return [
            'author_id' => ['sometimes', 'integer', 'exists:users,id'],
            'title' => ['sometimes', 'string'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'excerpt' => ['sometimes', 'nullable', 'string'],
            'content' => ['sometimes', 'nullable', 'string'],
            // Accept stored relative path or remote URL
            'featured_image_url' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'featured_image' => ['sometimes', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_featured_image' => ['sometimes', 'boolean'],
            'canonical_url' => ['sometimes', 'nullable', 'url'],
            'meta_title' => ['sometimes', 'nullable', 'string'],
            'meta_description' => ['sometimes', 'nullable', 'string'],
            'og_image_url' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'og_image' => ['sometimes', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_og_image' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'in:draft,published,scheduled,archived'],
            'published_at' => ['sometimes', 'nullable', 'date'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['string'],
            'categories' => ['sometimes', 'array'],
            'categories.*' => ['string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('status')) {
            $status = Str::lower(trim((string) $this->input('status')));
            if ($status === '') {
                $this->request->remove('status');
            } else {
                $this->merge(['status' => $status]);
            }
        }
    }
}
