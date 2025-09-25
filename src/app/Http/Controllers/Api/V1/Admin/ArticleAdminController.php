<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Article\Models\Article;
use App\Domain\Article\Models\Category;
use App\Domain\Article\Models\Tag;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Article\StoreArticleRequest;
use App\Http\Requests\Article\UpdateArticleRequest;
use App\Http\Resources\ArticleResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ArticleAdminController extends ApiController
{
    public function store(StoreArticleRequest $request)
    {
        $data = $request->validated();

        $slug = $data['slug'] ?? null;
        if (! $slug) {
            $slug = Article::makeUniqueSlug($data['title']);
        }

        $reading = self::estimateReading($data['content'] ?? '');

        $article = new Article();
        $article->fill(array_merge($data, [
            'slug' => $slug,
            'author_id' => $request->user()->id,
            'reading_time' => $reading['minutes'],
            'word_count' => $reading['words'],
        ]));
        $article->save();

        $this->syncTaxonomies($article, $data);

        $article->load(['tags:id,slug,name', 'categories:id,slug,name', 'author:id,name']);

        // Invalidate caches
        Cache::tags(['articles'])->flush();

        return $this->respond(new ArticleResource($article), 201);
    }

    public function update(UpdateArticleRequest $request, Article $article)
    {
        $data = $request->validated();

        if (isset($data['title']) && empty($data['slug'])) {
            // Update slug only if not provided explicitly
            $data['slug'] = Article::makeUniqueSlug($data['title'], $article->id);
        }

        if (array_key_exists('content', $data)) {
            $reading = self::estimateReading((string) $data['content']);
            $data['reading_time'] = $reading['minutes'];
            $data['word_count'] = $reading['words'];
        }

        $article->fill($data);
        $article->save();

        $this->syncTaxonomies($article, $data);
        $article->load(['tags:id,slug,name', 'categories:id,slug,name', 'author:id,name']);

        Cache::tags(['articles'])->flush();

        return $this->respond(new ArticleResource($article));
    }

    public function destroy(Article $article)
    {
        $article->delete();
        Cache::tags(['articles'])->flush();
        return response()->noContent();
    }

    protected function syncTaxonomies(Article $article, array $data): void
    {
        if (array_key_exists('tags', $data)) {
            $tagIds = collect($data['tags'] ?? [])
                ->filter()
                ->map(fn ($slugOrName) => (string) $slugOrName)
                ->map(function ($value) {
                    $slug = Str::slug($value);
                    $tag = Tag::firstOrCreate(['slug' => $slug], ['name' => $value]);
                    return $tag->id;
                })->all();
            $article->tags()->sync($tagIds);
        }

        if (array_key_exists('categories', $data)) {
            $categoryIds = collect($data['categories'] ?? [])
                ->filter()
                ->map(fn ($slugOrName) => (string) $slugOrName)
                ->map(function ($value) {
                    $slug = Str::slug($value);
                    $cat = Category::firstOrCreate(['slug' => $slug], ['name' => $value]);
                    return $cat->id;
                })->all();
            $article->categories()->sync($categoryIds);
        }
    }

    protected static function estimateReading(string $html): array
    {
        $text = strip_tags($html);
        $words = str_word_count($text);
        $minutes = max(1, (int) ceil($words / 225));
        return ['words' => $words, 'minutes' => $minutes];
    }
}
