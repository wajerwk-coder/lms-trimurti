<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah soft delete (deleted_at) ke tabel-tabel yang belum punya.
 *
 * Tabel yang mendapat soft delete:
 *   - attendances           (log absensi — bisa di-recover jika salah input)
 *   - assignment_submissions (pengumpulan tugas — tidak boleh hilang permanen)
 *   - class_students        (enrollment siswa — penting untuk audit)
 *   - class_subjects        (jadwal kelas — perlu history)
 *   - guru_subjects         (mapping guru-mapel — perlu audit)
 *   - material_downloads    (log unduhan — archive saja, tidak perlu hard delete)
 *   - practical_assessments (penilaian — data sensitif, jangan hard delete)
 *
 * Tabel yang TIDAK diberi soft delete (by design):
 *   - sessions   (expired sessions cukup di-prune, tidak perlu recovery)
 */
return new class extends Migration
{
    private array $tables = [
        'attendances',
        'assignment_submissions',
        'class_students',
        'class_subjects',
        'guru_subjects',
        'material_downloads',
        'practical_assessments',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->softDeletes();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropSoftDeletes();
                });
            }
        }
    }
};
