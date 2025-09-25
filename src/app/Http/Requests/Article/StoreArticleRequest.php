<?php

namespace App\Http\Requests\Article;

use Illuminate\Foundation\Http\FormRequest;

class StoreArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null; // guarded by middleware/policies
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'featured_image_url' => ['nullable', 'url'],
            'canonical_url' => ['nullable', 'url'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'og_image_url' => ['nullable', 'url'],
            'status' => ['required', 'in:draft,published,scheduled,archived'],
            'published_at' => ['nullable', 'date'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'], // slugs or names
            'categories' => ['nullable', 'array'],
            'categories.*' => ['string'], // slugs or names
        ];
    }
}

