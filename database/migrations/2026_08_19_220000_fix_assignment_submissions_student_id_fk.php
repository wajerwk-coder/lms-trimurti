<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fix FK student_id di assignment_submissions:
 * Sebelumnya FK ke tabel 'users' (lama), sekarang FK ke 'users_central'.
 * Juga tambah kolom siswa_id jika belum ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            // Drop FK lama ke tabel users jika ada
            try {
                $table->dropForeign(['student_id']);
            } catch (\Throwable $e) {
                // Mungkin sudah tidak ada, lanjut
            }

            // Hapus unique constraint lama [assignment_id, student_id] jika ada
            try {
                $table->dropUnique('as_unique_submission');
            } catch (\Throwable $e) {
                // Mungkin sudah tidak ada
            }

            // Buat FK student_id ke users_central
            // student_id sekarang nullable karena kita pakai siswa_id sebagai primary
            if (Schema::hasColumn('assignment_submissions', 'student_id')) {
                // Ubah jadi nullable dulu agar tidak error
                $table->unsignedBigInteger('student_id')->nullable()->change();
            }

            // Pastikan siswa_id ada
            if (!Schema::hasColumn('assignment_submissions', 'siswa_id')) {
                $table->unsignedBigInteger('siswa_id')->nullable()->after('student_id');
            }

            // Unique constraint baru berdasarkan assignment_id + siswa_id
            try {
                $table->unique(['assignment_id', 'siswa_id'], 'as_unique_siswa_submission');
            } catch (\Throwable $e) {
                // Mungkin sudah ada
            }
        });
    }

    public function down(): void
    {
        // Tidak di-rollback untuk keamanan
    }
};
