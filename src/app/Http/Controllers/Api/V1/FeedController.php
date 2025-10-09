<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Article\Models\Article;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Response;

class FeedController extends ApiController
{
    public function articlesRss(): Response
    {
        $xml = Cache::tags(['articles','feeds'])->remember('api.v1.feeds.articles.rss', 120, function () {
            $items = $this->latestArticles();
            return $this->renderRss($items);
        });
        return response($xml, 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
    }

    public function articlesAtom(): Response
    {
        $xml = Cache::tags(['articles','feeds'])->remember('api.v1.feeds.articles.atom', 120, function () {
            $items = $this->latestArticles();
            return $this->renderAtom($items);
        });
        return response($xml, 200, ['Content-Type' => 'application/atom+xml; charset=UTF-8']);
    }

    protected function latestArticles()
    {
        return Article::published()
            ->orderByDesc('published_at')
            ->limit(50)
            ->get(['id','slug','title','excerpt','content','published_at','updated_at','canonical_url','meta_description','featured_image_url']);
    }

    protected function siteUrl(): string
    {
        return rtrim(config('app.url'), '/');
    }

    protected function itemUrl($article): string
    {
        return $article->canonical_url ?: $this->siteUrl().'/articles/'.$article->slug;
    }

    protected function renderRss($items): string
    {
        $channelTitle = config('app.name').' Articles';
        $site = $this->siteUrl();
        $lastBuild = optional($items->first()?->updated_at ?: now())->toRssString();
        $xml = [];
        $xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml[] = '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
        $xml[] = '<channel>';
        $xml[] = '<title>'.e($channelTitle).'</title>';
        $xml[] = '<link>'.$site.'/articles</link>';
        $xml[] = '<description>'.e(config('app.name')).' latest articles</description>';
        $xml[] = '<lastBuildDate>'.$lastBuild.'</lastBuildDate>';
        $xml[] = '<atom:link href="'.$site.'/api/v1/feeds/articles.rss" rel="self" type="application/rss+xml" />';
        foreach ($items as $a) {
            $xml[] = '<item>';
            $xml[] = '<title>'.e($a->title).'</title>';
            $xml[] = '<link>'.$this->itemUrl($a).'</link>';
            $xml[] = '<guid isPermaLink="true">'.$this->itemUrl($a).'</guid>';
            $xml[] = '<pubDate>'.optional($a->published_at)->toRssString().'</pubDate>';
            $desc = $a->meta_description ?: $a->excerpt ?: strip_tags((string)$a->content);
            $xml[] = '<description>'.e($desc).'</description>';
            $xml[] = '</item>';
        }
        $xml[] = '</channel>';
        $xml[] = '</rss>';
        return implode("\n", $xml);
    }

    protected function renderAtom($items): string
    {
        $site = $this->siteUrl();
        $feedId = $site.'/';
        $updated = optional($items->first()?->updated_at ?: now())->toAtomString();
        $xml = [];
        $xml[] = '<?xml version="1.0" encoding="utf-8"?>';
        $xml[] = '<feed xmlns="http://www.w3.org/2005/Atom">';
        $xml[] = '<title>'.e(config('app.name').' Articles').'</title>';
        $xml[] = '<link href="'.$site.'/api/v1/feeds/articles.atom" rel="self" />';
        $xml[] = '<link href="'.$site.'/articles" />';
        $xml[] = '<updated>'.$updated.'</updated>';
        $xml[] = '<id>'.$feedId.'</id>';
        foreach ($items as $a) {
            $xml[] = '<entry>';
            $xml[] = '<title>'.e($a->title).'</title>';
            $xml[] = '<link href="'.$this->itemUrl($a).'" />';
            $xml[] = '<id>'.$this->itemUrl($a).'</id>';
            $xml[] = '<updated>'.optional($a->updated_at ?: $a->published_at)->toAtomString().'</updated>';
            $summary = $a->meta_description ?: $a->excerpt ?: strip_tags((string)$a->content);
            $xml[] = '<summary>'.e($summary).'</summary>';
            $xml[] = '</entry>';
        }
        $xml[] = '</feed>';
        return implode("\n", $xml);
    }
}
