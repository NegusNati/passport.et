<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Article\Models\Article;
use App\Domain\Article\Models\Category;
use App\Domain\Article\Models\Tag;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Article\StoreArticleRequest;
use App\Http\Requests\Article\UpdateArticleRequest;
use App\Http\Resources\ArticleResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleAdminController extends ApiController
{
    public function store(StoreArticleRequest $request)
    {
        $validated = $request->validated();
        $data = [];

        // Extract only the fields that are present in validated data
        $fillableKeys = [
            'author_id', 'title', 'slug', 'excerpt', 'content',
            'featured_image_url', 'canonical_url', 'meta_title',
            'meta_description', 'og_image_url', 'status', 'published_at'
        ];

        foreach ($fillableKeys as $key) {
            if (array_key_exists($key, $validated)) {
                $data[$key] = $validated[$key];
            }
        }

        $slug = $data['slug'] ?? null;
        if (! $slug) {
            $slug = Article::makeUniqueSlug($data['title']);
        }

        $reading = self::estimateReading($data['content'] ?? '');

        $authorId = (int) ($data['author_id'] ?? $request->user()->id);

        $article = new Article();

        // Handle file uploads first; files take precedence over *_url fields
        $uploadChanges = $this->handleUploads($request);

        $article->fill(array_merge($data, $uploadChanges, [
            'slug' => $slug,
            'author_id' => $authorId,
            'reading_time' => $reading['minutes'],
            'word_count' => $reading['words'],
        ]));
        $article->save();

        $this->syncTaxonomies($article, $request);

        $article->refresh()->load([
            'tags:id,slug,name',
            'categories:id,slug,name',
            'author:id,first_name,last_name,email',
        ]);

        // Invalidate caches
        Cache::tags(['articles'])->flush();

        return $this->respond(new ArticleResource($article), 201);
    }

    public function update(UpdateArticleRequest $request, Article $article)
    {
        $validated = $request->validated();
        $data = [];

        // Extract only the fields that are present in validated data
        $fillableKeys = [
            'author_id', 'title', 'slug', 'excerpt', 'content',
            'featured_image_url', 'canonical_url', 'meta_title',
            'meta_description', 'og_image_url', 'status', 'published_at'
        ];

        foreach ($fillableKeys as $key) {
            if (array_key_exists($key, $validated)) {
                $data[$key] = $validated[$key];
            }
        }

        if (isset($data['title']) && empty($data['slug'])) {
            // Update slug only if not provided explicitly
            $data['slug'] = Article::makeUniqueSlug($data['title'], $article->id);
        }

        if (array_key_exists('content', $data)) {
            $reading = self::estimateReading((string) $data['content']);
            $data['reading_time'] = $reading['minutes'];
            $data['word_count'] = $reading['words'];
        }

        if (array_key_exists('author_id', $data)) {
            $data['author_id'] = (int) $data['author_id'];
        }

        // Apply uploads/removals; may delete old files if replaced or removed
        $uploadChanges = $this->handleUploads($request, $article);

        $article->fill(array_merge($data, $uploadChanges));

        if ($article->isDirty()) {
            $article->save();
        }

        $this->syncTaxonomies($article, $request);

        $article->refresh()->load([
            'tags:id,slug,name',
            'categories:id,slug,name',
            'author:id,first_name,last_name,email',
        ]);

        Cache::tags(['articles'])->flush();

        return $this->respond(new ArticleResource($article));
    }

    public function destroy(Article $article)
    {
        $article->delete();
        Cache::tags(['articles'])->flush();
        return response()->noContent();
    }

    protected function syncTaxonomies(Article $article, Request $request): void
    {
        if ($request->exists('tags')) {
            $tagIds = collect($request->input('tags', []))
                ->filter()
                ->map(fn ($slugOrName) => (string) $slugOrName)
                ->map(function ($value) {
                    $slug = Str::slug($value);
                    $tag = Tag::firstOrCreate(['slug' => $slug], ['name' => $value]);
                    return $tag->id;
                })->all();
            $article->tags()->sync($tagIds);
        }

        if ($request->exists('categories')) {
            $categoryIds = collect($request->input('categories', []))
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

    /**
     * Handle featured and OG image uploads/removals.
     * Returns an array of model attribute changes (e.g., ['featured_image_url' => 'articles/featured/..']).
     */
    protected function handleUploads($request, ?Article $existing = null): array
    {
        $changes = [];

        // Featured image
        if ($request->boolean('remove_featured_image')) {
            if ($existing && $this->isStoredPath($existing->featured_image_url)) {
                Storage::disk('public')->delete($existing->featured_image_url);
            }
            $changes['featured_image_url'] = null;
        }

        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('articles/featured', 'public');
            if ($existing && $this->isStoredPath($existing->featured_image_url)) {
                Storage::disk('public')->delete($existing->featured_image_url);
            }
            $changes['featured_image_url'] = $path; // store relative path; resource resolves to public URL
        }

        // OG image
        if ($request->boolean('remove_og_image')) {
            if ($existing && $this->isStoredPath($existing->og_image_url)) {
                Storage::disk('public')->delete($existing->og_image_url);
            }
            $changes['og_image_url'] = null;
        }

        if ($request->hasFile('og_image')) {
            $path = $request->file('og_image')->store('articles/og', 'public');
            if ($existing && $this->isStoredPath($existing->og_image_url)) {
                Storage::disk('public')->delete($existing->og_image_url);
            }
            $changes['og_image_url'] = $path;
        }

        return $changes;
    }

    protected function isStoredPath(?string $value): bool
    {
        if (! $value) return false;
        return ! Str::startsWith($value, ['http://', 'https://', 'data:']);
    }
}
