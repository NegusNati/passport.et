<?php

namespace App\Observers;

use App\Domain\Article\Models\Article;
use Illuminate\Support\Facades\Cache;

class ArticleObserver
{
    public function created(Article $article): void
    {
        Cache::tags(['articles','feeds'])->flush();
    }

    public function updated(Article $article): void
    {
        Cache::tags(['articles','feeds'])->flush();
    }

    public function deleted(Article $article): void
    {
        Cache::tags(['articles','feeds'])->flush();
    }

    public function restored(Article $article): void
    {
        Cache::tags(['articles','feeds'])->flush();
    }
}
