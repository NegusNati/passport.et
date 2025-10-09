<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('advertisement_requests', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number');
            $table->string('email')->nullable();
            $table->string('full_name');
            $table->string('company_name')->nullable();
            $table->text('description');
            $table->string('file_path')->nullable();
            $table->string('status')->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'phone_number']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisement_requests');
    }
};
