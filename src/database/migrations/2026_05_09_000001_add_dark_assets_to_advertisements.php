<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('advertisements', function (Blueprint $table): void {
            $table->string('ad_desktop_dark_asset')->nullable()->after('ad_desktop_asset');
            $table->string('ad_mobile_dark_asset')->nullable()->after('ad_mobile_asset');
        });
    }

    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table): void {
            $table->dropColumn([
                'ad_desktop_dark_asset',
                'ad_mobile_dark_asset',
            ]);
        });
    }
};
