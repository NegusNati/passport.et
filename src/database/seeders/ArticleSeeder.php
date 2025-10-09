<?php

namespace Database\Seeders;

use App\Domain\Article\Models\Article;
use App\Domain\Article\Models\Category;
use App\Domain\Article\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $authorId = null; // set to an existing user ID if desired

        $samples = [
            [
                'title' => 'How to Apply for an Ethiopian Passport (2025 Guide)',
                'excerpt' => 'Step-by-step instructions to prepare your application and avoid delays.',
                'content' => '<p>This guide walks you through documents, fees, and timelines...</p>',
                'status' => 'published',
                'published_at' => now()->subDays(2),
                'categories' => ['Passports'],
                'tags' => ['Guides', 'How-To'],
            ],
            [
                'title' => 'Passport Appointment Tips: What To Bring',
                'excerpt' => 'Make the most of your appointment with this checklist.',
                'content' => '<p>Bring original IDs, photographs, and payment receipts...</p>',
                'status' => 'published',
                'published_at' => now()->subDays(5),
                'categories' => ['Appointments', 'Passports'],
                'tags' => ['Checklist'],
            ],
            [
                'title' => 'Processing Timeline Updates – September 2025',
                'excerpt' => 'Latest processing times and regional variations.',
                'content' => '<p>Processing timelines have improved in Addis Ababa...</p>',
                'status' => 'published',
                'published_at' => now()->subDay(),
                'categories' => ['Passports'],
                'tags' => ['News', 'Updates'],
            ],
            [
                'title' => 'How to Prepare Visa Documents for Schengen',
                'excerpt' => 'A concise checklist to keep you organized.',
                'content' => '<p>Bank statements, travel insurance, and itinerary are essential...</p>',
                'status' => 'draft',
                'published_at' => null,
                'categories' => ['Visas'],
                'tags' => ['Guides', 'Checklist'],
            ],
        ];

        foreach ($samples as $s) {
            $slug = Article::makeUniqueSlug($s['title']);
            $reading = self::estimateReading($s['content']);

            $article = Article::updateOrCreate(
                ['slug' => $slug],
                [
                    'author_id' => $authorId,
                    'title' => $s['title'],
                    'excerpt' => $s['excerpt'],
                    'content' => $s['content'],
                    'status' => $s['status'],
                    'published_at' => $s['published_at'],
                    'reading_time' => $reading['minutes'],
                    'word_count' => $reading['words'],
                    'meta_title' => $s['title'],
                    'meta_description' => Str::limit(strip_tags($s['excerpt'] ?: $s['content']), 160),
                ]
            );

            if (! empty($s['categories'])) {
                $catIds = collect($s['categories'])->map(function ($name) {
                    $slug = Str::slug($name);
                    return Category::firstOrCreate(['slug' => $slug], ['name' => $name])->id;
                })->all();
                $article->categories()->sync($catIds);
            }

            if (! empty($s['tags'])) {
                $tagIds = collect($s['tags'])->map(function ($name) {
                    $slug = Str::slug($name);
                    return Tag::firstOrCreate(['slug' => $slug], ['name' => $name])->id;
                })->all();
                $article->tags()->sync($tagIds);
            }
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
