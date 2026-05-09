<?php

namespace Database\Seeders;

use App\Domain\Advertisement\Models\AdSlot;
use App\Domain\Advertisement\Models\Advertisement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class AdFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAdminUser();
        $this->seedSlots();
        $this->seedCreativeFiles();
        $this->seedAdvertisements();
    }

    protected function seedAdminUser(): void
    {
        $admin = User::updateOrCreate(
            ['phone_number' => '0911111111'],
            [
                'first_name' => 'Ad',
                'last_name' => 'Admin',
                'email' => 'ads-admin@passport.test',
                'password' => Hash::make('password'),
            ],
        );

        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        if (! $admin->hasRole($role->name)) {
            $admin->assignRole($role);
        }
    }

    protected function seedSlots(): void
    {
        $slots = [
            ['code' => 'home-alerts-banner', 'name' => 'Home Alerts Banner', 'page_context' => 'landing', 'format' => 'horizontal', 'desktop_width' => 1200, 'desktop_height' => 300, 'mobile_width' => 640, 'mobile_height' => 360],
            ['code' => 'home-download-app', 'name' => 'Home Download App', 'page_context' => 'landing', 'format' => 'horizontal', 'desktop_width' => 1200, 'desktop_height' => 300, 'mobile_width' => 640, 'mobile_height' => 360],
            ['code' => 'articles-list-top', 'name' => 'Articles List Top', 'page_context' => 'articles', 'format' => 'horizontal', 'desktop_width' => 1200, 'desktop_height' => 300, 'mobile_width' => 640, 'mobile_height' => 360],
            ['code' => 'article-mobile-top', 'name' => 'Article Mobile Top', 'page_context' => 'article-detail', 'format' => 'horizontal', 'desktop_width' => 1200, 'desktop_height' => 300, 'mobile_width' => 640, 'mobile_height' => 360],
            ['code' => 'article-inline-bottom', 'name' => 'Article Inline Bottom', 'page_context' => 'article-detail', 'format' => 'horizontal', 'desktop_width' => 1200, 'desktop_height' => 300, 'mobile_width' => 640, 'mobile_height' => 360],
            ['code' => 'article-sidebar', 'name' => 'Article Sidebar', 'page_context' => 'article-detail', 'format' => 'vertical', 'desktop_width' => 320, 'desktop_height' => 640, 'mobile_width' => 640, 'mobile_height' => 360],
            ['code' => 'passports-list-below-latest', 'name' => 'Passports List Below Latest', 'page_context' => 'passports', 'format' => 'horizontal', 'desktop_width' => 1200, 'desktop_height' => 300, 'mobile_width' => 640, 'mobile_height' => 360],
            ['code' => 'passport-detail-result', 'name' => 'Passport Result Banner', 'page_context' => 'passport-detail', 'format' => 'horizontal', 'desktop_width' => 1200, 'desktop_height' => 300, 'mobile_width' => 640, 'mobile_height' => 360],
            ['code' => 'locations-directory-bottom', 'name' => 'Locations Directory Bottom', 'page_context' => 'locations', 'format' => 'horizontal', 'desktop_width' => 1200, 'desktop_height' => 300, 'mobile_width' => 640, 'mobile_height' => 360],
            ['code' => 'calendar-sidebar-primary', 'name' => 'Calendar Sidebar Primary', 'page_context' => 'calendar', 'format' => 'vertical', 'desktop_width' => 320, 'desktop_height' => 640, 'mobile_width' => 640, 'mobile_height' => 360],
            ['code' => 'calendar-inline', 'name' => 'Calendar Inline Banner', 'page_context' => 'calendar', 'format' => 'horizontal', 'desktop_width' => 1200, 'desktop_height' => 300, 'mobile_width' => 640, 'mobile_height' => 360],
            ['code' => 'calendar-sidebar-secondary', 'name' => 'Calendar Sidebar Secondary', 'page_context' => 'calendar', 'format' => 'vertical', 'desktop_width' => 320, 'desktop_height' => 640, 'mobile_width' => 640, 'mobile_height' => 360],
        ];

        foreach ($slots as $slot) {
            AdSlot::updateOrCreate(
                ['code' => $slot['code']],
                array_merge($slot, ['is_active' => true]),
            );
        }
    }

    protected function seedCreativeFiles(): void
    {
        $creatives = [
            'home-alerts-banner' => ['title' => 'Passport Alerts', 'color' => '#065f46'],
            'home-download-app' => ['title' => 'Passport.ET App', 'color' => '#1d4ed8'],
            'article-sidebar' => ['title' => 'Travel Guides', 'color' => '#7c2d12'],
            'passports-list-below-latest' => ['title' => 'Passport Services', 'color' => '#0369a1'],
            'passport-detail-result' => ['title' => 'Status Alerts', 'color' => '#4338ca'],
            'locations-directory-bottom' => ['title' => 'Branch Office Ads', 'color' => '#0e7490'],
            'calendar-inline' => ['title' => 'Calendar Tools', 'color' => '#0f766e'],
        ];

        foreach ($creatives as $code => $creative) {
            Storage::disk('public')->put(
                "advertisements/desktop/{$code}.svg",
                $this->svg($creative['title'], $creative['color'], 1200, 300),
            );
            Storage::disk('public')->put(
                "advertisements/mobile/{$code}.svg",
                $this->svg($creative['title'], $creative['color'], 640, 360),
            );
        }
    }

    protected function seedAdvertisements(): void
    {
        $ads = [
            ['slot_code' => 'home-alerts-banner', 'ad_title' => 'Advertise with Passport Alerts', 'target_url' => 'http://localhost:3000/advertisement-requests', 'priority' => 90],
            ['slot_code' => 'home-download-app', 'ad_title' => 'Download the Passport.ET App', 'target_url' => 'http://localhost:3000/#download', 'priority' => 80],
            ['slot_code' => 'article-sidebar', 'ad_title' => 'Passport.ET Travel Guides', 'target_url' => 'http://localhost:3000/articles', 'priority' => 70],
            ['slot_code' => 'passports-list-below-latest', 'ad_title' => 'Promote Passport Services', 'target_url' => 'http://localhost:3000/advertisement-requests', 'priority' => 78],
            ['slot_code' => 'passport-detail-result', 'ad_title' => 'Get Passport Status Alerts', 'target_url' => 'http://localhost:3000/advertisement-requests', 'priority' => 75],
            ['slot_code' => 'locations-directory-bottom', 'ad_title' => 'Reach Passport Location Visitors', 'target_url' => 'http://localhost:3000/advertisement-requests', 'priority' => 72],
            ['slot_code' => 'calendar-inline', 'ad_title' => 'Use Ethiopian Calendar Tools', 'target_url' => 'http://localhost:3000/calendar', 'priority' => 65],
        ];

        foreach ($ads as $ad) {
            Advertisement::updateOrCreate(
                ['ad_slot_number' => 'local-'.$ad['slot_code']],
                [
                    'slot_code' => $ad['slot_code'],
                    'ad_title' => $ad['ad_title'],
                    'alt_text' => $ad['ad_title'].' promotion',
                    'ad_excerpt' => 'Local development advertisement for '.$ad['slot_code'],
                    'ad_desc' => 'Seeded by AdFeatureSeeder for local ad management testing.',
                    'ad_desktop_asset' => "advertisements/desktop/{$ad['slot_code']}.svg",
                    'desktop_width' => 1200,
                    'desktop_height' => 300,
                    'ad_mobile_asset' => "advertisements/mobile/{$ad['slot_code']}.svg",
                    'mobile_width' => 640,
                    'mobile_height' => 360,
                    'ad_client_link' => $ad['target_url'],
                    'target_url' => $ad['target_url'],
                    'status' => Advertisement::STATUS_ACTIVE,
                    'package_type' => Advertisement::PACKAGE_MONTHLY,
                    'ad_published_date' => now()->subDay()->toDateString(),
                    'ad_ending_date' => now()->addMonths(3)->toDateString(),
                    'payment_status' => Advertisement::PAYMENT_PAID,
                    'payment_amount' => 0,
                    'client_name' => 'Passport.ET',
                    'priority' => $ad['priority'],
                    'admin_notes' => 'Local seeded ad. Safe to edit during ad feature testing.',
                ],
            );
        }
    }

    protected function svg(string $title, string $color, int $width, int $height): string
    {
        $escapedTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}" role="img" aria-label="{$escapedTitle}">
  <rect width="{$width}" height="{$height}" fill="{$color}"/>
  <circle cx="{$width}" cy="0" r="{$height}" fill="#ffffff" opacity="0.12"/>
  <circle cx="0" cy="{$height}" r="{$height}" fill="#ffffff" opacity="0.10"/>
  <text x="48" y="112" fill="#ffffff" font-family="Arial, sans-serif" font-size="34" font-weight="700">Passport.ET</text>
  <text x="48" y="178" fill="#ffffff" font-family="Arial, sans-serif" font-size="56" font-weight="800">{$escapedTitle}</text>
  <text x="48" y="234" fill="#dcfce7" font-family="Arial, sans-serif" font-size="28">Local dynamic advertisement</text>
</svg>
SVG;
    }
}
