<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('practical_scores') && !Schema::hasColumn('practical_scores', 'academic_period_id')) {
            Schema::table('practical_scores', function (Blueprint $table) {
                $table->unsignedBigInteger('academic_period_id')
                      ->nullable()
                      ->after('id')
                      ->comment('FK ke academic_periods.id — periode aktif saat nilai dibuat');
                $table->index('academic_period_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('practical_scores') && Schema::hasColumn('practical_scores', 'academic_period_id')) {
            Schema::table('practical_scores', function (Blueprint $table) {
                $table->dropIndex(['academic_period_id']);
                $table->dropColumn('academic_period_id');
            });
        }
    }
};
