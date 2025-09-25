<?php

namespace App\Support;

class ArticleFilters
{
    public const DEFAULT_SORT_COLUMN = 'published_at';
    public const DEFAULT_SORT_DIRECTION = 'desc';
    public const DEFAULT_LIMIT = 20;
    public const DEFAULT_PER_PAGE = 20;
    public const CACHE_TTL = 120; // seconds

    public static function sortableColumns(): array
    {
        return [
            'published_at',
            'created_at',
            'updated_at',
            'title',
        ];
    }

    public static function defaultSort(): array
    {
        return [self::DEFAULT_SORT_COLUMN, self::DEFAULT_SORT_DIRECTION];
    }

    public static function defaultLimit(): int
    {
        return self::DEFAULT_LIMIT;
    }

    public static function defaultPerPage(): int
    {
        return self::DEFAULT_PER_PAGE;
    }

    public static function cacheTtl(): int
    {
        return self::CACHE_TTL;
    }
}

