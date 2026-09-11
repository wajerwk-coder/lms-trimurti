<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Konsolidasi jurusan:
 *  - Pastikan semua baris di classes punya jurusan_id terisi (dari major_id jika kosong)
 *  - Pastikan semua baris di subjects punya jurusan_id terisi (dari major_id jika kosong)
 *  - Drop FK classes.major_id → majors dan subjects.major_id → majors
 *  - Drop kolom major_id dari classes dan subjects
 *  - Drop tabel majors (tidak ada lagi FK yang menunjuk ke sana)
 *
 *  CATATAN: tabel jurusans tetap ada sebagai single source of truth.
 *  Model Kelas.major() (backward compat relasi) dihapus — pakai Kelas.jurusan().
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // ── 1. Sinkronisasi final: isi jurusan_id dari major_id jika masih null ──
        if (Schema::hasColumn('classes', 'jurusan_id') && Schema::hasColumn('classes', 'major_id')) {
            DB::statement("
                UPDATE classes
                SET jurusan_id = major_id
                WHERE jurusan_id IS NULL AND major_id IS NOT NULL
            ");
        }

        if (Schema::hasColumn('subjects', 'jurusan_id') ?? false) {
            // subjects.jurusan_id belum tentu ada — skip jika tidak ada
        }

        // ── 2. Drop FK classes.major_id → majors (jika ada) ─────────────────────
        if (Schema::hasTable('classes') && Schema::hasColumn('classes', 'major_id')) {
            $fkExists = DB::select("
                SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'classes'
                  AND CONSTRAINT_NAME IN (
                    'classes_major_id_foreign',
                    'classes_major_id_fk'
                  )
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ");

            if (!empty($fkExists)) {
                Schema::table('classes', function (Blueprint $table) use ($fkExists) {
                    $table->dropForeign($fkExists[0]->CONSTRAINT_NAME);
                });
            }

            // Drop index major_id di classes jika ada
            $idxExists = DB::select("
                SELECT INDEX_NAME FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'classes'
                  AND INDEX_NAME = 'classes_major_id_index'
                LIMIT 1
            ");
            if (!empty($idxExists)) {
                Schema::table('classes', function (Blueprint $table) {
                    $table->dropIndex('classes_major_id_index');
                });
            }

            // Drop kolom major_id dari classes
            Schema::table('classes', function (Blueprint $table) {
                $table->dropColumn('major_id');
            });
        }

        // ── 3. Drop FK subjects.major_id → majors (jika ada) ────────────────────
        if (Schema::hasTable('subjects') && Schema::hasColumn('subjects', 'major_id')) {
            $fkExists = DB::select("
                SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'subjects'
                  AND CONSTRAINT_NAME IN (
                    'subjects_major_id_foreign',
                    'subjects_major_id_fk'
                  )
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ");

            if (!empty($fkExists)) {
                Schema::table('subjects', function (Blueprint $table) use ($fkExists) {
                    $table->dropForeign($fkExists[0]->CONSTRAINT_NAME);
                });
            }

            // Drop index major_id di subjects jika ada
            $idxExists = DB::select("
                SELECT INDEX_NAME FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'subjects'
                  AND INDEX_NAME = 'subjects_major_id_index'
                LIMIT 1
            ");
            if (!empty($idxExists)) {
                Schema::table('subjects', function (Blueprint $table) {
                    $table->dropIndex('subjects_major_id_index');
                });
            }

            // Drop kolom major_id dari subjects
            Schema::table('subjects', function (Blueprint $table) {
                $table->dropColumn('major_id');
            });
        }

        // ── 4. Drop tabel majors (sudah tidak ada FK yang menunjuk ke sana) ──────
        Schema::dropIfExists('majors');

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Re-create tabel majors
        Schema::create('majors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Re-populate dari jurusans
        DB::statement("
            INSERT INTO majors (id, name, code, description, created_at, updated_at)
            SELECT id, name, code, description, created_at, updated_at FROM jurusans
        ");

        // Tambah kembali major_id ke classes
        Schema::table('classes', function (Blueprint $table) {
            $table->unsignedBigInteger('major_id')->nullable()->after('grade');
            $table->index('major_id');
        });
        DB::statement("UPDATE classes SET major_id = jurusan_id WHERE jurusan_id IS NOT NULL");

        // Tambah kembali major_id ke subjects
        Schema::table('subjects', function (Blueprint $table) {
            $table->unsignedBigInteger('major_id')->nullable()->after('code');
            $table->index('major_id');
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
