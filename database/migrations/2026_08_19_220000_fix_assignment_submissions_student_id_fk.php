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
        // ── 1. Drop FK student_id hanya jika benar-benar ada ────────────────
        $fkExists = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME   = 'assignment_submissions'
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
              AND CONSTRAINT_NAME IN (
                  'assignment_submissions_student_id_foreign',
                  'assignment_submissions_student_id_fk'
              )
        ");

        if (!empty($fkExists)) {
            Schema::table('assignment_submissions', function (Blueprint $table) use ($fkExists) {
                foreach ($fkExists as $fk) {
                    $table->dropForeign($fk->CONSTRAINT_NAME);
                }
            });
        }

        // ── 2. Drop unique constraint lama jika ada ──────────────────────────
        $uniqueExists = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME   = 'assignment_submissions'
              AND CONSTRAINT_TYPE = 'UNIQUE'
              AND CONSTRAINT_NAME = 'as_unique_submission'
        ");

        if (!empty($uniqueExists)) {
            Schema::table('assignment_submissions', function (Blueprint $table) {
                $table->dropUnique('as_unique_submission');
            });
        }

        // ── 3. Ubah student_id jadi nullable ────────────────────────────────
        if (Schema::hasColumn('assignment_submissions', 'student_id')) {
            Schema::table('assignment_submissions', function (Blueprint $table) {
                $table->unsignedBigInteger('student_id')->nullable()->change();
            });
        }

        // ── 4. Tambah siswa_id jika belum ada ───────────────────────────────
        if (!Schema::hasColumn('assignment_submissions', 'siswa_id')) {
            Schema::table('assignment_submissions', function (Blueprint $table) {
                $table->unsignedBigInteger('siswa_id')->nullable()->after('student_id');
            });
        }

        // ── 5. Tambah unique constraint baru jika belum ada ─────────────────
        $newUniqueExists = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME   = 'assignment_submissions'
              AND CONSTRAINT_TYPE = 'UNIQUE'
              AND CONSTRAINT_NAME = 'as_unique_siswa_submission'
        ");

        if (empty($newUniqueExists)) {
            Schema::table('assignment_submissions', function (Blueprint $table) {
                $table->unique(['assignment_id', 'siswa_id'], 'as_unique_siswa_submission');
            });
        }
    }

    public function down(): void
    {
        // Tidak di-rollback untuk keamanan
    }
};
