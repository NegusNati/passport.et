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

    // Articles
    public static function articleSearch(string $hash): string
    {
        return "articles.search.$hash";
    }

    public static function articlesList(int $page): string
    {
        return "articles.list.page.$page";
    }

    public static function articlesByCategory(string $categorySlug, int $page): string
    {
        return sprintf('articles.category.%s.page.%d', md5($categorySlug), $page);
    }

    public static function categoriesList(): string
    {
        return 'articles.categories.list';
    }

    public static function tagsList(): string
    {
        return 'articles.tags.list';
    }

    // Advertisement Requests
    public static function advertisementSearch(string $hash): string
    {
        return "advertisements.search.$hash";
    }

    public static function advertisementsList(int $page): string
    {
        return "advertisements.list.page.$page";
    }

    // Advertisement CRM (Ad Slots)
    public static function adCrmSearch(string $hash): string
    {
        return "ad_crm.search.$hash";
    }

    public static function adCrmList(int $page): string
    {
        return "ad_crm.list.page.$page";
    }

    public static function adCrmBySlot(string $slotNumber): string
    {
        return "ad_crm.slot.$slotNumber";
    }

    public static function adCrmActiveSlots(): string
    {
        return 'ad_crm.active_slots';
    }
}
