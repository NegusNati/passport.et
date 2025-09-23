<?php

namespace App\Support;

class PassportFilters
{
    public const DEFAULT_SORT_COLUMN = 'dateOfPublish';
    public const DEFAULT_SORT_DIRECTION = 'desc';
    public const DEFAULT_LIMIT = 60;
    public const DEFAULT_PER_PAGE = 25;
    public const CACHE_TTL = 60; // seconds

    public static function sortableColumns(): array
    {
        return [
            'dateOfPublish',
            'requestNumber',
            'created_at',
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
