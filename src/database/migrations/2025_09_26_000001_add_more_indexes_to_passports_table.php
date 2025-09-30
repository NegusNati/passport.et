<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('p_d_f_to_s_q_lites', function (Blueprint $table) {
            // Individual indexes to speed up single-field prefix lookups
            $table->index('middleName');
            $table->index('lastName');
            $table->index('dateOfPublish');

            // Composite index to accelerate first+last name lookups when middle name is absent
            $table->index(['lastName', 'firstName']);
        });
    }

    public function down(): void
    {
        Schema::table('p_d_f_to_s_q_lites', function (Blueprint $table) {
            $table->dropIndex(['middleName']);
            $table->dropIndex(['lastName']);
            $table->dropIndex(['dateOfPublish']);
            $table->dropIndex(['lastName', 'firstName']);
        });
    }
};
