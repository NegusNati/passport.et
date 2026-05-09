<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_slots', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('page_context')->nullable();
            $table->string('format')->default('horizontal');
            $table->unsignedInteger('desktop_width')->default(1200);
            $table->unsignedInteger('desktop_height')->default(300);
            $table->unsignedInteger('mobile_width')->default(640);
            $table->unsignedInteger('mobile_height')->default(360);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('ad_slots')->insert($this->defaultSlots());

        Schema::table('advertisements', function (Blueprint $table): void {
            $table->string('slot_code')->nullable()->after('ad_slot_number')->index();
            $table->string('target_url')->nullable()->after('ad_client_link');
            $table->string('alt_text')->nullable()->after('ad_title');
            $table->unsignedInteger('desktop_width')->nullable()->after('ad_desktop_asset');
            $table->unsignedInteger('desktop_height')->nullable()->after('desktop_width');
            $table->unsignedInteger('mobile_width')->nullable()->after('ad_mobile_asset');
            $table->unsignedInteger('mobile_height')->nullable()->after('mobile_width');
            $table->index(['slot_code', 'status', 'ad_published_date', 'ad_ending_date'], 'advertisements_slot_active_idx');
        });

        DB::table('advertisements')->update([
            'slot_code' => DB::raw('ad_slot_number'),
            'target_url' => DB::raw('ad_client_link'),
            'alt_text' => DB::raw('ad_title'),
        ]);
    }

    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table): void {
            $table->dropIndex('advertisements_slot_active_idx');
            $table->dropIndex(['slot_code']);
            $table->dropColumn([
                'slot_code',
                'target_url',
                'alt_text',
                'desktop_width',
                'desktop_height',
                'mobile_width',
                'mobile_height',
            ]);
        });

        Schema::dropIfExists('ad_slots');
    }

    private function defaultSlots(): array
    {
        $now = now();

        return [
            [
                'code' => 'home-alerts-banner',
                'name' => 'Home alerts banner',
                'page_context' => 'landing',
                'format' => 'horizontal',
                'desktop_width' => 1200,
                'desktop_height' => 300,
                'mobile_width' => 640,
                'mobile_height' => 360,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'home-download-app',
                'name' => 'Home download app banner',
                'page_context' => 'landing',
                'format' => 'horizontal',
                'desktop_width' => 1200,
                'desktop_height' => 300,
                'mobile_width' => 640,
                'mobile_height' => 360,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'articles-list-top',
                'name' => 'Articles list top banner',
                'page_context' => 'articles',
                'format' => 'horizontal',
                'desktop_width' => 1200,
                'desktop_height' => 300,
                'mobile_width' => 640,
                'mobile_height' => 360,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'article-mobile-top',
                'name' => 'Article mobile top banner',
                'page_context' => 'articles',
                'format' => 'horizontal',
                'desktop_width' => 1200,
                'desktop_height' => 300,
                'mobile_width' => 640,
                'mobile_height' => 360,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'article-inline-bottom',
                'name' => 'Article inline bottom banner',
                'page_context' => 'articles',
                'format' => 'horizontal',
                'desktop_width' => 1200,
                'desktop_height' => 300,
                'mobile_width' => 640,
                'mobile_height' => 360,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'article-sidebar',
                'name' => 'Article sidebar',
                'page_context' => 'articles',
                'format' => 'vertical',
                'desktop_width' => 320,
                'desktop_height' => 640,
                'mobile_width' => 640,
                'mobile_height' => 360,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'passports-list-below-latest',
                'name' => 'Passports list below latest banner',
                'page_context' => 'passports',
                'format' => 'horizontal',
                'desktop_width' => 1200,
                'desktop_height' => 300,
                'mobile_width' => 640,
                'mobile_height' => 360,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'passport-detail-result',
                'name' => 'Passport detail result banner',
                'page_context' => 'passports',
                'format' => 'horizontal',
                'desktop_width' => 1200,
                'desktop_height' => 300,
                'mobile_width' => 640,
                'mobile_height' => 360,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'locations-directory-bottom',
                'name' => 'Locations directory bottom banner',
                'page_context' => 'locations',
                'format' => 'horizontal',
                'desktop_width' => 1200,
                'desktop_height' => 300,
                'mobile_width' => 640,
                'mobile_height' => 360,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'calendar-sidebar-primary',
                'name' => 'Calendar primary sidebar',
                'page_context' => 'calendar',
                'format' => 'vertical',
                'desktop_width' => 320,
                'desktop_height' => 640,
                'mobile_width' => 640,
                'mobile_height' => 360,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'calendar-inline',
                'name' => 'Calendar inline banner',
                'page_context' => 'calendar',
                'format' => 'horizontal',
                'desktop_width' => 1200,
                'desktop_height' => 300,
                'mobile_width' => 640,
                'mobile_height' => 360,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'calendar-sidebar-secondary',
                'name' => 'Calendar secondary sidebar',
                'page_context' => 'calendar',
                'format' => 'vertical',
                'desktop_width' => 320,
                'desktop_height' => 640,
                'mobile_width' => 640,
                'mobile_height' => 360,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
    }
};
