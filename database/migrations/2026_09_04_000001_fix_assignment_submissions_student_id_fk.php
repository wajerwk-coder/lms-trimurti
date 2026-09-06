<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fix FK constraint: student_id di assignment_submissions references tabel users (lama)
 * tapi semua user sekarang ada di users_central.
 * Solusi: drop FK constraint student_id, ubah menjadi kolom biasa.
 * siswa_id (FK ke users_central) sudah ada dan sudah benar.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Cek apakah FK assignment_submissions_student_id_foreign ada
        // sebelum mencoba drop (Railway DB mungkin sudah tidak punya FK ini)
        $fkExists = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'assignment_submissions'
              AND CONSTRAINT_NAME = 'assignment_submissions_student_id_foreign'
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ");

        if (!empty($fkExists)) {
            Schema::table('assignment_submissions', function (Blueprint $table) {
                $table->dropForeign('assignment_submissions_student_id_foreign');
            });
        }

        // Sync student_id dengan siswa_id untuk data yang sudah ada
        // (agar tidak ada NULL atau mismatch)
        DB::statement("
            UPDATE assignment_submissions
            SET student_id = siswa_id
            WHERE siswa_id IS NOT NULL AND (student_id IS NULL OR student_id != siswa_id)
        ");
    }

    public function down(): void
    {
        // Tidak perlu rollback FK lama
    }
};
