<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            // Change title from string(255) to text for longer titles
            $table->text('title')->change();
            
            // Change meta_title from string(255) to text
            $table->text('meta_title')->nullable()->change();
            
            // Ensure meta_description and excerpt are text (should already be, but explicit)
            $table->text('meta_description')->nullable()->change();
            $table->text('excerpt')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            // Revert to original string lengths
            $table->string('title', 255)->change();
            $table->string('meta_title', 255)->nullable()->change();
            $table->text('meta_description')->nullable()->change();
            $table->text('excerpt')->nullable()->change();
        });
    }
};
