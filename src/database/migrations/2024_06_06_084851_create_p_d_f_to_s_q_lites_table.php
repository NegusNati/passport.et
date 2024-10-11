<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('p_d_f_to_s_q_lites', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->integer('no')->nullable();
            $table->string('firstName')->nullable();
            $table->string('middleName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('requestNumber')->unique();
            $table->string('location')->nullable();
            $table->date('dateOfPublish');
            $table->timestamps();

            // Indexes for faster search
            $table->index(['location']);
            $table->index('firstName');
            $table->index(['firstName', 'middleName', 'lastName']);
            $table->index('requestNumber');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p_d_f_to_s_q_lites');
    }
};
