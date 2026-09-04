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
        // Drop FK constraint student_id yang references tabel users lama
        Schema::table('assignment_submissions', function (Blueprint $table) {
            // Cek apakah FK ada sebelum drop
            try {
                $table->dropForeign('assignment_submissions_student_id_foreign');
            } catch (\Throwable $e) {
                // FK mungkin sudah tidak ada, lanjutkan
            }
        });

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
