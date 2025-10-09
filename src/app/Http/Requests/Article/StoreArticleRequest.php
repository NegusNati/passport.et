<?php

namespace App\Http\Requests\Article;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null; // guarded by middleware/policies
    }

    public function rules(): array
    {
        return [
            'author_id' => ['sometimes', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            // Allow either a remote URL or a stored relative path; URL validation would reject storage paths
            'featured_image_url' => ['nullable', 'string', 'max:2048'],
            'featured_image' => ['sometimes', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'canonical_url' => ['nullable', 'url'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'og_image_url' => ['nullable', 'string', 'max:2048'],
            'og_image' => ['sometimes', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'status' => ['required', 'in:draft,published,scheduled,archived'],
            'published_at' => ['nullable', 'date'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'], // slugs or names
            'categories' => ['nullable', 'array'],
            'categories.*' => ['string'], // slugs or names
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
