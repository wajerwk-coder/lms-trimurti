<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Index untuk siswa.kelas_id + deleted_at — dipakai withCount('siswa')
        Schema::table('siswa', function (Blueprint $table) {
            if (!$this->indexExists('siswa', 'siswa_kelas_id_deleted_at_index')) {
                $table->index(['kelas_id', 'deleted_at'], 'siswa_kelas_id_deleted_at_index');
            }
        });

        // Index untuk classes — dipakai filter dan query
        Schema::table('classes', function (Blueprint $table) {
            if (!$this->indexExists('classes', 'classes_jurusan_id_index')) {
                $table->index('jurusan_id', 'classes_jurusan_id_index');
            }
            if (!$this->indexExists('classes', 'classes_status_index')) {
                $table->index('status', 'classes_status_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropIndexIfExists('siswa_kelas_id_deleted_at_index');
        });
        Schema::table('classes', function (Blueprint $table) {
            $table->dropIndexIfExists('classes_jurusan_id_index');
            $table->dropIndexIfExists('classes_status_index');
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = \Illuminate\Support\Facades\DB::select(
            "SHOW INDEX FROM `$table` WHERE Key_name = ?", [$indexName]
        );
        return count($indexes) > 0;
    }
};
