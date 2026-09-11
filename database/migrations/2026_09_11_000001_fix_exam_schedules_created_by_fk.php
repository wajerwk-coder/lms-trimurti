<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fix FK exam_schedules_new.created_by yang masih menunjuk ke tabel users (sudah di-drop).
 * Ganti FK agar menunjuk ke users_central.id.
 *
 * Selain itu, cek juga tabel-tabel lain yang masih punya FK ke users (sisa dari drop migration).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // ── exam_schedules_new.created_by ─────────────────────────────────
        if (Schema::hasTable('exam_schedules_new') && Schema::hasColumn('exam_schedules_new', 'created_by')) {
            // Drop FK lama ke users jika masih ada
            $fk = DB::select("
                SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'exam_schedules_new'
                  AND CONSTRAINT_NAME = 'exam_schedules_new_created_by_foreign'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ");

            if (!empty($fk)) {
                Schema::table('exam_schedules_new', function (Blueprint $table) {
                    $table->dropForeign('exam_schedules_new_created_by_foreign');
                });
            }

            // Pastikan kolom nullable dulu (required untuk ON DELETE SET NULL)
            Schema::table('exam_schedules_new', function (Blueprint $table) {
                $table->unsignedBigInteger('created_by')->nullable()->change();
            });

            // Tambah FK baru ke users_central
            Schema::table('exam_schedules_new', function (Blueprint $table) {
                $table->foreign('created_by')
                      ->references('id')->on('users_central')
                      ->onDelete('set null');
            });
        }

        // ── class_students.student_id → users_central ────────────────────
        // (FK ini yang juga menghalangi drop tabel users sebelumnya)
        if (Schema::hasTable('class_students') && Schema::hasColumn('class_students', 'student_id')) {
            $fk = DB::select("
                SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'class_students'
                  AND CONSTRAINT_NAME = 'class_students_student_id_foreign'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ");

            if (!empty($fk)) {
                Schema::table('class_students', function (Blueprint $table) {
                    $table->dropForeign('class_students_student_id_foreign');
                });

                // Tambah FK baru ke users_central
                Schema::table('class_students', function (Blueprint $table) {
                    $table->foreign('student_id')
                          ->references('id')->on('users_central')
                          ->onDelete('cascade');
                });
            }
        }

        // ── Scan semua FK yang masih menunjuk ke tabel users (jika ada) ──
        $orphanFKs = DB::select("
            SELECT TABLE_NAME, CONSTRAINT_NAME
            FROM information_schema.REFERENTIAL_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE()
              AND REFERENCED_TABLE_NAME = 'users'
        ");

        foreach ($orphanFKs as $fk) {
            try {
                Schema::table($fk->TABLE_NAME, function (Blueprint $table) use ($fk) {
                    $table->dropForeign($fk->CONSTRAINT_NAME);
                });
            } catch (\Throwable $e) {
                // Abaikan jika sudah tidak ada
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        // Tidak perlu rollback — tabel users sudah tidak ada
    }
};
