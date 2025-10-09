<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->string('ad_slot_number')->unique();
            $table->string('ad_title');
            $table->text('ad_desc')->nullable();
            $table->text('ad_excerpt')->nullable();
            $table->string('ad_desktop_asset')->nullable();
            $table->string('ad_mobile_asset')->nullable();
            $table->string('ad_client_link')->nullable();
            $table->string('status')->default('draft');
            $table->string('package_type');
            $table->date('ad_published_date')->nullable();
            $table->date('ad_ending_date')->nullable();
            $table->string('payment_status')->default('pending');
            $table->decimal('payment_amount', 10, 2)->nullable();
            
            // Additional useful fields
            $table->string('client_name')->nullable();
            $table->foreignId('advertisement_request_id')->nullable()->constrained('advertisement_requests')->nullOnDelete();
            $table->unsignedBigInteger('impressions_count')->default(0);
            $table->unsignedBigInteger('clicks_count')->default(0);
            $table->integer('priority')->default(0);
            $table->boolean('expiry_notification_sent')->default(false);
            $table->text('admin_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'ad_published_date', 'ad_ending_date']);
            $table->index('ad_slot_number');
            $table->index('package_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
