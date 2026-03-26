<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('p_d_f_to_s_q_lites', function (Blueprint $table): void {
            $table->string('applicationNumber')->nullable()->after('requestNumber');
            $table->string('sourceSurname')->nullable()->after('applicationNumber');
            $table->string('sourceGivenname')->nullable()->after('sourceSurname');
            $table->string('sourceFormat', 32)->nullable()->after('sourceGivenname');
            $table->foreignId('importBatchId')->nullable()->after('sourceFormat')
                ->constrained('passport_import_batches')->nullOnDelete();

            $table->index('applicationNumber');
            $table->index('sourceFormat');
        });
    }

    public function down(): void
    {
        Schema::table('p_d_f_to_s_q_lites', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('importBatchId');
            $table->dropIndex(['applicationNumber']);
            $table->dropIndex(['sourceFormat']);
            $table->dropColumn([
                'applicationNumber',
                'sourceSurname',
                'sourceGivenname',
                'sourceFormat',
            ]);
        });
    }
};
