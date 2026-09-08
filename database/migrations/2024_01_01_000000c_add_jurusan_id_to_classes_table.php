<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom jurusan_id dan status ke tabel classes.
 * FK siswa→users_central DIHAPUS dari migration ini karena data existing
 * di Railway tidak konsisten (siswa.user_id mungkin tidak ada di users_central).
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah jurusan_id ke classes jika belum ada
        if (Schema::hasTable('classes') && !Schema::hasColumn('classes', 'jurusan_id')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->unsignedBigInteger('jurusan_id')->nullable()->after('major_id');
            });

            // Tambah FK hanya jika tabel jurusans ada DAN tidak ada data orphan
            if (Schema::hasTable('jurusans')) {
                try {
                    Schema::table('classes', function (Blueprint $table) {
                        $table->foreign('jurusan_id')
                              ->references('id')->on('jurusans')
                              ->onDelete('set null');
                    });
                } catch (\Throwable $e) {
                    // FK gagal karena data tidak konsisten — skip, kolom tetap ada tanpa FK
                    \Illuminate\Support\Facades\Log::warning('FK jurusan_id skip: ' . $e->getMessage());
                }
            }
        }

        // 2. Tambah kolom status ke classes jika belum ada
        if (Schema::hasTable('classes') && !Schema::hasColumn('classes', 'status')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->string('status', 20)->default('active')->after('academic_year');
            });
        }

        // 3. FK siswa→users_central DIHAPUS dari sini.
        //    Data di Railway mungkin tidak konsisten sehingga FK constraint
        //    selalu gagal. Relasi ini dihandle di level aplikasi (model).
    }

    public function down(): void
    {
        if (Schema::hasTable('classes')) {
            // Drop FK jurusan_id jika ada
            $fkExists = \Illuminate\Support\Facades\DB::select("
                SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME   = 'classes'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                  AND CONSTRAINT_NAME LIKE '%jurusan_id%'
            ");
            if (!empty($fkExists)) {
                Schema::table('classes', function (Blueprint $table) use ($fkExists) {
                    foreach ($fkExists as $fk) {
                        $table->dropForeign($fk->CONSTRAINT_NAME);
                    }
                });
            }

            if (Schema::hasColumn('classes', 'jurusan_id')) {
                Schema::table('classes', function (Blueprint $table) {
                    $table->dropColumn('jurusan_id');
                });
            }
            if (Schema::hasColumn('classes', 'status')) {
                Schema::table('classes', function (Blueprint $table) {
                    $table->dropColumn('status');
                });
            }
        }
    }
};
