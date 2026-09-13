<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom academic_period_id ke semua tabel aktivitas
 * agar setiap aktivitas guru/siswa tercatat dalam periode pembelajaran aktif.
 */
return new class extends Migration
{
    private array $tables = [
        'materials',
        'assignments',
        'attendances',
        'practicals',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tbl) {
            if (!Schema::hasTable($tbl)) continue;
            if (Schema::hasColumn($tbl, 'academic_period_id')) continue;

            Schema::table($tbl, function (Blueprint $table) {
                $table->unsignedBigInteger('academic_period_id')
                      ->nullable()
                      ->after('id')
                      ->comment('FK ke academic_periods.id — periode aktif saat data dibuat');
                $table->index('academic_period_id', 'idx_' . $table->getTable() . '_period');
            });
        }

        // exam_schedules_new terpisah karena nama tabel perlu cek
        if (Schema::hasTable('exam_schedules_new') && !Schema::hasColumn('exam_schedules_new', 'academic_period_id')) {
            Schema::table('exam_schedules_new', function (Blueprint $table) {
                $table->unsignedBigInteger('academic_period_id')->nullable()->after('id');
                $table->index('academic_period_id');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tbl) {
            if (!Schema::hasTable($tbl)) continue;
            if (!Schema::hasColumn($tbl, 'academic_period_id')) continue;
            Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                try { $table->dropIndex('idx_' . $tbl . '_period'); } catch (\Exception $e) {}
                $table->dropColumn('academic_period_id');
            });
        }

        if (Schema::hasTable('exam_schedules_new') && Schema::hasColumn('exam_schedules_new', 'academic_period_id')) {
            Schema::table('exam_schedules_new', function (Blueprint $table) {
                try { $table->dropIndex(['academic_period_id']); } catch (\Exception $e) {}
                $table->dropColumn('academic_period_id');
            });
        }
    }
};
