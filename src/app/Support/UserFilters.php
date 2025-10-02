<?php

namespace App\Support;

class UserFilters
{
    public static function sortableColumns(): array
    {
        return [
            'created_at',
            'updated_at',
            'first_name',
            'last_name',
            'email',
            'id',
        ];
    }

    public static function defaultSort(): array
    {
        return ['created_at', 'desc'];
    }

    public static function perPageOptions(): array
    {
        return [10, 25, 50, 100];
    }

    public static function defaultPerPage(): int
    {
        return 25;
    }
}
