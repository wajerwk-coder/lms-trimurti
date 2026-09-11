<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Bersihkan SEMUA foreign key yang masih menunjuk ke tabel `users` (sudah di-drop).
 * Scan seluruh database, drop FK orphan, lalu optionally re-create ke users_central.
 *
 * Tabel yang diketahui masih punya FK ke users:
 *   - sessions.user_id                    → cukup drop FK (sessions tidak butuh constraint ketat)
 *   - class_students.student_id           → ganti ke users_central, nullable
 *   - notifications.penerima_id           → cukup drop FK (sudah nullable)
 *   - notifications.created_by            → cukup drop FK
 *   - password_reset_tokens (jika ada)    → drop saja
 *
 * Semua operasi aman karena:
 *   - SET FOREIGN_KEY_CHECKS=0 di awal
 *   - Setiap dropForeign dibungkus try-catch
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // ── Scan semua FK yang masih menunjuk ke tabel `users` ────────────
        $orphans = DB::select("
            SELECT
                kcu.TABLE_NAME,
                kcu.CONSTRAINT_NAME,
                kcu.COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE kcu
            JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
                ON kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME
                AND kcu.TABLE_SCHEMA   = rc.CONSTRAINT_SCHEMA
            WHERE kcu.TABLE_SCHEMA        = DATABASE()
              AND kcu.REFERENCED_TABLE_NAME = 'users'
        ");

        foreach ($orphans as $fk) {
            try {
                DB::statement("ALTER TABLE `{$fk->TABLE_NAME}` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
                \Illuminate\Support\Facades\Log::info("Dropped orphan FK: {$fk->TABLE_NAME}.{$fk->CONSTRAINT_NAME}");
            } catch (\Throwable $e) {
                // Sudah di-drop atau tidak ada — abaikan
            }
        }

        // ── Pastikan kolom-kolom kunci nullable ────────────────────────────
        // sessions.user_id — tidak perlu FK constraint, biarkan saja
        // class_students.student_id — ganti FK ke users_central jika ada
        if (Schema::hasTable('class_students') && Schema::hasColumn('class_students', 'student_id')) {
            // Cek apakah FK ke users_central sudah ada
            $existingFk = DB::select("
                SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'class_students'
                  AND CONSTRAINT_NAME = 'class_students_student_id_foreign'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ");

            if (empty($existingFk)) {
                // Buat FK baru ke users_central
                try {
                    Schema::table('class_students', function (Blueprint $table) {
                        $table->unsignedBigInteger('student_id')->nullable()->change();
                        $table->foreign('student_id')
                              ->references('id')->on('users_central')
                              ->onDelete('cascade');
                    });
                } catch (\Throwable $e) {
                    // Abaikan jika gagal — tidak kritis
                }
            }
        }

        // ── Tabel lain yang FK-nya ke users sudah tidak relevan ─────────── 
        // sessions — tidak perlu FK sama sekali, user_id hanya referensi lunak
        // notifications — penerima_id dan created_by sudah nullable, tidak butuh FK

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        // Tidak perlu rollback — tabel users sudah tidak ada
    }
};
