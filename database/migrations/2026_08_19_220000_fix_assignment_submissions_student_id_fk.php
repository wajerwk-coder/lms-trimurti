<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fix FK student_id di assignment_submissions.
 *
 * Masalah: student_id FK ke tabel `users` (lama) tapi user disimpan di `users_central`.
 * Solusi:  Drop FK constraint student_id, buat ulang FK ke users_central.
 * Efek:    Pengumpulan tugas tidak lagi gagal karena FK violation.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            // Drop FK lama ke tabel users
            try {
                $table->dropForeign('assignment_submissions_student_id_foreign');
            } catch (\Throwable $e) {
                // Mungkin sudah tidak ada
            }
        });

        // Sync: isi student_id dengan nilai dari siswa_id (users_central.id)
        // agar konsisten — student_id sekarang sama nilainya dengan siswa_id
        DB::statement("
            UPDATE assignment_submissions
            SET student_id = siswa_id
            WHERE siswa_id IS NOT NULL
              AND (student_id IS NULL OR student_id != siswa_id)
        ");

        Schema::table('assignment_submissions', function (Blueprint $table) {
            // Buat FK baru ke users_central
            try {
                $table->foreign('student_id')
                      ->references('id')
                      ->on('users_central')
                      ->onDelete('cascade');
            } catch (\Throwable $e) {
                // Jika FK sudah ada atau gagal, biarkan nullable saja
            }
        });
    }

    public function down(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            try {
                $table->dropForeign('assignment_submissions_student_id_foreign');
            } catch (\Throwable $e) {}

            try {
                $table->foreign('student_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade');
            } catch (\Throwable $e) {}
        });
    }
};
