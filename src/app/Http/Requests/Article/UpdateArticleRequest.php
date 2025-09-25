<?php

namespace App\Http\Requests\Article;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null; // guarded elsewhere
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'excerpt' => ['sometimes', 'nullable', 'string'],
            'content' => ['sometimes', 'nullable', 'string'],
            'featured_image_url' => ['sometimes', 'nullable', 'url'],
            'canonical_url' => ['sometimes', 'nullable', 'url'],
            'meta_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'meta_description' => ['sometimes', 'nullable', 'string'],
            'og_image_url' => ['sometimes', 'nullable', 'url'],
            'status' => ['sometimes', 'in:draft,published,scheduled,archived'],
            'published_at' => ['sometimes', 'nullable', 'date'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['string'],
            'categories' => ['sometimes', 'array'],
            'categories.*' => ['string'],
        ];
    }
}

