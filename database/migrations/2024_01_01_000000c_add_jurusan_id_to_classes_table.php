<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom jurusan_id ke tabel classes dan update FK siswa ke users_central.
 * Migration ini dijalankan setelah users_central, jurusans, gurus dibuat.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Tambah jurusan_id ke classes jika belum ada
        if (Schema::hasTable('classes') && !Schema::hasColumn('classes', 'jurusan_id')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->unsignedBigInteger('jurusan_id')->nullable()->after('major_id');
                $table->foreign('jurusan_id')
                      ->references('id')->on('jurusans')
                      ->onDelete('set null');
            });
        }

        // Tambah kolom academic_year ke classes jika belum ada (di luar sudah ada tapi pastikan)
        if (Schema::hasTable('classes') && !Schema::hasColumn('classes', 'status')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->string('status', 20)->default('active')->after('academic_year');
            });
        }

        // Fix tabel siswa: FK user_id seharusnya ke users_central, bukan users lama
        // Cek apakah FK lama masih ada, drop dulu sebelum buat baru
        if (Schema::hasTable('siswa')) {
            $fkExists = \Illuminate\Support\Facades\DB::select("
                SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'siswa'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                  AND CONSTRAINT_NAME LIKE '%user_id%'
            ");

            if (!empty($fkExists)) {
                Schema::table('siswa', function (Blueprint $table) use ($fkExists) {
                    foreach ($fkExists as $fk) {
                        $table->dropForeign($fk->CONSTRAINT_NAME);
                    }
                });
            }

            // Tambah FK baru ke users_central jika users_central sudah ada
            if (Schema::hasTable('users_central')) {
                // Bersihkan data orphan: set user_id ke null jika tidak ada di users_central
                // agar penambahan FK tidak gagal karena constraint violation.
                \Illuminate\Support\Facades\DB::table('siswa')
                    ->whereNotNull('user_id')
                    ->whereNotIn('user_id', function ($query) {
                        $query->select('id')->from('users_central');
                    })
                    ->update(['user_id' => null]);

                $newFkExists = \Illuminate\Support\Facades\DB::select("
                    SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = 'siswa'
                      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                      AND CONSTRAINT_NAME = 'siswa_user_id_foreign_central'
                ");

                if (empty($newFkExists)) {
                    Schema::table('siswa', function (Blueprint $table) {
                        $table->foreign('user_id', 'siswa_user_id_foreign_central')
                              ->references('id')->on('users_central')
                              ->onDelete('cascade');
                    });
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('classes') && Schema::hasColumn('classes', 'jurusan_id')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->dropForeign(['jurusan_id']);
                $table->dropColumn('jurusan_id');
            });
        }
    }
};
