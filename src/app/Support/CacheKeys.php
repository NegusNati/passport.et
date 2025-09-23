<?php

namespace App\Support;

class CacheKeys
{
    public static function passportSearch(string $hash): string
    {
        return "passports.search.$hash";
    }

    public static function passportCount(): string
    {
        return 'passports.count';
    }

    public static function passportsAll(int $page): string
    {
        return "passports.all.page.$page";
    }

    public static function passportsByLocation(string $location, int $page): string
    {
        return sprintf('passports.location.%s.page.%d', md5($location), $page);
    }

    public static function locationsList(): string
    {
        return 'passports.locations.list';
    }
}
