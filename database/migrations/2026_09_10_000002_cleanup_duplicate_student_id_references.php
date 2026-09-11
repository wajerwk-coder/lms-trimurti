<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Bersihkan kolom ganda referensi siswa:
 *
 *  assignment_submissions:
 *    - student_id (FK lama ke users — sudah di-drop FK-nya di migration sebelumnya)
 *    - siswa_id   (FK aktif ke users_central.id) ← KOLOM YANG DIPERTAHANKAN
 *    → Drop student_id
 *
 *  attendances:
 *    - student_id (kolom lama, FK ke users)
 *    - siswa_id   (kolom aktif, menyimpan users_central.id)
 *    - tanggal    (alias dari date — keduanya menyimpan nilai yang sama)
 *    → Drop student_id; pertahankan siswa_id dan tanggal (karena banyak query pakai keduanya)
 *
 *  practical_scores:
 *    - siswa_id sudah konsisten ke users_central.id, tidak ada duplikat
 *    → Tidak ada aksi di tabel ini
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // ── assignment_submissions: drop kolom student_id ────────────────────
        if (Schema::hasTable('assignment_submissions') && Schema::hasColumn('assignment_submissions', 'student_id')) {
            // Pastikan siswa_id sinkron dulu
            DB::statement("
                UPDATE assignment_submissions
                SET siswa_id = student_id
                WHERE siswa_id IS NULL AND student_id IS NOT NULL
            ");

            Schema::table('assignment_submissions', function (Blueprint $table) {
                // Drop FK jika masih ada (defensive)
                $fkExists = DB::select("
                    SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = 'assignment_submissions'
                      AND CONSTRAINT_NAME = 'assignment_submissions_student_id_foreign'
                      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                ");
                if (!empty($fkExists)) {
                    $table->dropForeign('assignment_submissions_student_id_foreign');
                }

                // Drop index student_id jika ada
                $idxExists = DB::select("
                    SELECT INDEX_NAME FROM information_schema.STATISTICS
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = 'assignment_submissions'
                      AND INDEX_NAME = 'assignment_submissions_student_id_index'
                    LIMIT 1
                ");
                if (!empty($idxExists)) {
                    $table->dropIndex('assignment_submissions_student_id_index');
                }

                $table->dropColumn('student_id');
            });
        }

        // ── attendances: drop kolom student_id (bukan siswa_id) ─────────────
        if (Schema::hasTable('attendances') && Schema::hasColumn('attendances', 'student_id')) {
            // Pastikan siswa_id sinkron dulu
            DB::statement("
                UPDATE attendances
                SET siswa_id = student_id
                WHERE siswa_id IS NULL AND student_id IS NOT NULL
            ");

            Schema::table('attendances', function (Blueprint $table) {
                $fkExists = DB::select("
                    SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = 'attendances'
                      AND CONSTRAINT_NAME = 'attendances_student_id_foreign'
                      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                ");
                if (!empty($fkExists)) {
                    $table->dropForeign('attendances_student_id_foreign');
                }

                $idxExists = DB::select("
                    SELECT INDEX_NAME FROM information_schema.STATISTICS
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = 'attendances'
                      AND INDEX_NAME = 'attendances_student_id_index'
                    LIMIT 1
                ");
                if (!empty($idxExists)) {
                    $table->dropIndex('attendances_student_id_index');
                }

                $table->dropColumn('student_id');
            });
        }

        // ── attendances: drop kolom kelas_id (bukan FK di schema awal, jadi aman) ─
        // Kolom kelas_id di attendances tidak ada di schema awal — skip jika tidak ada
        // Biarkan subject_id dan siswa_id sebagai kolom yang dipertahankan

        // ── Pastikan siswa_id di attendances punya index ─────────────────────
        if (Schema::hasTable('attendances') && Schema::hasColumn('attendances', 'siswa_id')) {
            $idxExists = DB::select("
                SELECT INDEX_NAME FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'attendances'
                  AND INDEX_NAME = 'attendances_siswa_id_index'
                LIMIT 1
            ");
            if (empty($idxExists)) {
                Schema::table('attendances', function (Blueprint $table) {
                    $table->index('siswa_id', 'attendances_siswa_id_index');
                });
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Re-add student_id ke assignment_submissions
        if (Schema::hasTable('assignment_submissions') && !Schema::hasColumn('assignment_submissions', 'student_id')) {
            Schema::table('assignment_submissions', function (Blueprint $table) {
                $table->unsignedBigInteger('student_id')->nullable()->after('assignment_id');
                $table->index('student_id');
            });
            DB::statement("UPDATE assignment_submissions SET student_id = siswa_id WHERE siswa_id IS NOT NULL");
        }

        // Re-add student_id ke attendances
        if (Schema::hasTable('attendances') && !Schema::hasColumn('attendances', 'student_id')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->unsignedBigInteger('student_id')->nullable()->after('id');
                $table->index('student_id');
            });
            DB::statement("UPDATE attendances SET student_id = siswa_id WHERE siswa_id IS NOT NULL");
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
