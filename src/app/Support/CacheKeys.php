<?php

namespace App\Support;

class CacheKeys
{
    public static function passportSearch(string $hash): string
    {
        return "passports.search.$hash";
    }
}
