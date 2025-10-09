<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable(); // store sanitized HTML
            $table->string('featured_image_url')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('og_image_url')->nullable();
            $table->string('status')->default('draft'); // draft|published|scheduled|archived
            $table->timestamp('published_at')->nullable()->index();
            $table->unsignedInteger('reading_time')->default(0);
            $table->unsignedInteger('word_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
        });

        // Optional: MySQL fulltext for search fields
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE articles ADD FULLTEXT fulltext_title_excerpt_content (title, excerpt, content)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};

